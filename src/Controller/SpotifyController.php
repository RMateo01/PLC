<?php

namespace App\Controller;



use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\Null_;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class SpotifyController extends AbstractController
{
    private $spotifyParams;
    private $spotify;

    public function __construct()
    {

        $this->spotifyParams = [
            'client_id' => '1c3c4111128740eca6a3394577c53184',
            'client_secret' => '6153f7e8d2d343c0a2459c3a127a7df7',
            'scope' => ['user-read-email','user-read-private','playlist-read-private',
                'playlist-read-collaborative','playlist-modify-public',
                'playlist-modify-private','user-follow-read','user-follow-modify',
                'user-library-read','user-read-recently-played','user-top-read','user-library-modify',
                'user-read-birthdate']
        ];

        $this->spotify = new Session(
            $this->spotifyParams['client_id'],
            $this->spotifyParams['client_secret'],
            'http://127.0.0.1:8000/spotify/auth'
        );

    }



    /**
     * @Route("/spotify", name="spotify")
     */
    public function index()
    {
        return $this->render('spotify/index.html.twig', [
            'controller_name' => 'SpotifyController',
        ]);
    }

    /**
     * @Route("/spotify/connexion", name="connexion_spotify")
     */
    public function SpotifyConnect(SessionInterface $session){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');



        $options = [
            'scope' => $this->spotifyParams['scope']
        ];

        $spotify_auth_url = $this->spotify->getAuthorizeUrl($options);


        $user = $this->getUser();

        if($user->getAccessToken() != NULL && $user->getRefreshToken() != NULL){
            $accessToken = $user->getAccessToken();
            $refreshToken = $user->getRefreshToken();

            $session->set('accessToken', $accessToken);
            $session->set('refreshToken', $refreshToken);

            return $this->redirectToRoute('reseau');
        }

        return $this->render('spotify/SpotifyConnect.html.twig', array(
            'spotify_auth_url' => $spotify_auth_url,

        ));

    }


    /**
     * @Route("/spotify/auth", name="spotify_auth")
     */
    public function auth(SessionInterface $session,Request $request,ObjectManager $manager)
    {
        $user = new User();
        $user = $this->getUser();

        $accessCode = $request->get('code');
        $session->set('accessCode', $accessCode); // symfony session

        $this->spotify->requestAccessToken($accessCode);
        $accessToken = $this->spotify->getAccessToken();
        $session->set('accessToken', $accessToken);// symfony session

        $user->setAccessToken($accessToken);
        $manager->persist($user);
        $manager->flush();

        $refreshToken = $this->spotify->getRefreshToken();
        $session->set('refreshToken', $refreshToken); // symfony session

        $user->setRefreshToken($refreshToken);
        $manager->persist($user);
        $manager->flush();

        $api = $this->connectApi($session);

        $profilSpotify = $api->me();
        $user->setIdSpotify($profilSpotify->id);

        $manager->persist($user);
        $manager->flush();


        return $this->redirectToRoute('reseau');
    }


    /* Cette fonction permet de retourner une api connectée avec l'utilisateur connecté sur notre reseau social */
    public function connectApi(SessionInterface $session){

        $accessToken = $session->get('accessToken');
        $refreshToken = $session->get('refreshToken');

        if( !$accessToken ) {

            $this->redirectToRoute('connexion_spotify');
        }

        $api = new SpotifyWebAPI();

        try {
            $api->setAccessToken($accessToken);
            $test = $api->getNewReleases();
        }
        catch (\Exception $e) {

            $exeptionCode = $e->getCode();

            // The access token expired or Invalid access token
            if ($e->getCode() == 401) {
                //echo $e->getMessage();

                // Obtain new accessToken

                $sessionSpotify = new SpotifyController();

                $accessToken = $sessionSpotify->refreshToken($session);

                // store the newly received token
                $api->setAccessToken($accessToken);
                $test = $api->getNewReleases();
            }
        }

        return $api;

    }

    /* Cette fonction permet de retourner une api connecté avec un compte spotify neutre que l'on a crée exprès */
    public function connectApiNeutral(){

        $accessToken = 'BQBRkh6_xJidHx-IYvs2dh3lrWSkr-TIMUzT_b5UcXbzESsjc9LJjwWtLzmGlX08AUFPj1YAP6ALAE5em8jyzdce6Zcd8CPC9SENA2ylfYNOXM4eTO_NCHGJkPHcKDxjTUi2HoYyEtpdw90CTKI16-Xvct_Ea_XbPlorvsIS53hjIj92V3MRhCaP1ScD6K0Rf4MRI7Gh9SqIwUlLEMzpyKQDPaCdPZLFqB504aM3lQ7ZxQeRZwxm2SKvhLE5eo1LUwiA4HLPkeDijWjSPFgDPUof9jiB-Cg';
        $refreshToken = 'AQAjc9amwZQtOT3jDGrVUN6j7GXmLUgAWYCB8S4X2BklMUJ8QStVdRDANxND8nomR_uQPZdCMz8Yw83Mpy13rzyvCvGJ-ep-iUQBuB9BSWV-McaMFjTWwcCK2s5Xm4K5iHvnLQ';

        $api = new SpotifyWebAPI();

        try {
            $api->setAccessToken($accessToken);
            $test = $api->getNewReleases();
        }
        catch (\Exception $e) {

            $exeptionCode = $e->getCode();

            // The access token expired or Invalid access token
            if ($e->getCode() == 401) {
                //echo $e->getMessage();

                // Obtain new accessToken

                $sessionSpotify = new SpotifyController();

                $accessToken = $sessionSpotify->refreshTokenUser($refreshToken);

                // store the newly received token
                $api->setAccessToken($accessToken);
                $test = $api->getNewReleases();
            }
        }

        return $api;

    }

    public function refreshToken(SessionInterface $session){

        $refreshToken = $session->get('refreshToken');

        $this->spotify->refreshAccessToken($refreshToken);

        $accessToken = $this->spotify->getAccessToken();

        return $accessToken;

    }

    /* Cette fonction permet de créer une api connecté sous un utilisateur spécial dont on a passé les informations
        en paramètre. Nottament utilisé pour acceder aux playlist de tout utilisateur quand on doit les afficher sur
        la page profil */
    public function connectApiUser($accessToken,$refreshToken){

        $api = new SpotifyWebAPI();

        try {
            $api->setAccessToken($accessToken);
            $test = $api->getNewReleases();
        }
        catch (\Exception $e) {

            $exeptionCode = $e->getCode();

            // The access token expired or Invalid access token
            if ($e->getCode() == 401) {
                //echo $e->getMessage();

                // Obtain new accessToken

                $sessionSpotify = new SpotifyController();

                $accessToken = $sessionSpotify->refreshTokenUser($refreshToken);

                // store the newly received token
                $api->setAccessToken($accessToken);
                $test = $api->getNewReleases();
            }
        }

        return $api;

    }

    public function refreshTokenUser($refreshToken){

        $this->spotify->refreshAccessToken($refreshToken);

        $accessToken = $this->spotify->getAccessToken();

        return $accessToken;

    }

}
