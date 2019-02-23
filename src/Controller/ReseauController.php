<?php

namespace App\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use App\Entity\Comment;
use App\Entity\Flow;
use App\Entity\Publication;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PublicationType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ReseauController extends AbstractController
{
    /**
     * @Route("/reseau", name="reseau")
     */
    public function index(Request $request,ObjectManager $manager, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        /* On stock les nouvelles sortie d'album dans $releases */
        $releases = $api->getNewReleases([
            'country' => 'fr',
            'limit' => 10
        ]);

        /*On recupere les tracks de l'utilisateur */
        $tracks = $api->getMySavedTracks(['limit' => 5]);

        $tabTrack = array();

        /* On crée un tableau des tracks de l'utilisateur */
        foreach ($tracks->items as $track) {
            array_push($tabTrack, $track->track->id);
        }

        /* si l'utilisateur a deja des tracks on cherche des recommandation en fonction de ses tracks */
        if ($tabTrack != NULL){
            $recommendations = $api->getRecommendations([
                'seed_tracks' => $tabTrack
            ]);
        }
        /* Sinon on mets des tracks aléatoire */
        else{
            $defaultTrack = array('6DCZcSspjsKoFjzjrWoCdn','3IM7zXywZ6sRTtkRjRLxJ8','15JINEqzVMv3SvJTAXAKED');
            $recommendations = $api->getRecommendations([
                'seed_tracks' => $defaultTrack
            ]);
        }

        $repo = $this->getDoctrine()->getRepository(Publication::class);

        $newPublication = new Publication();

        /* On crée le formulaire pour créer une nouvelle publication */
        $formPublication = $this->createForm(PublicationType::class, $newPublication);

        $formPublication->handleRequest($request);

        $user= new User();

        $user = $this->getUser();/* On recupere les informations de l'user connecté */


        /* On regarde si le formulaire de publication a été envoyé ou non, si oui on crée une nouvelle publication */
        if($formPublication->isSubmitted() && $formPublication->isValid()){

            $newPublication->setAuteur($user);
            $newPublication->setDateCreation(new \DateTime());

            $manager->persist($newPublication);
            $manager->flush();

            return $this->redirect($request->getUri());

        }

        $publication = $repo->findAll();/* On recupère toutes les publications */

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Publication::class);

        $listPubli = $repository->findAll();


        $repoComment = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Comment::class);

        $formsComment = array();
        $publiCom = array();

        /* On crée un nouvelle formulaire de commentaire pour chaque publication affichées */
        foreach ($listPubli as $Publi) {

            $idPubli[$Publi->getId()] = $Publi;

            ${"newComment" . $Publi->getId()} = new Comment();

            ${"form" . $Publi->getId()} = $this->createForm(CommentType::class, ${"newComment" . $Publi->getId()});


            ${"form" . $Publi->getId()} = $this->get('form.factory')->createNamed('form'.$Publi->getId(),CommentType::class,${"newComment" . $Publi->getId()});

            ${"form" . $Publi->getId()}->handleRequest($request);


            $formsComment[$Publi->getId()] = ${"form" . $Publi->getId()}->createView();

            /* On regarde pour chaque formulaire s'ils ont été envoyés ou non, si oui on crée un nouveau commentaire */
            if(${"form" . $Publi->getId()}->isSubmitted() && ${"form" . $Publi->getId()}->isValid()){

                $user= new User();

                $user = $this->getUser();

                $publicationCom = $repo = $this->getDoctrine()->getRepository(Publication::class)->find($Publi->getId());


                ${"newComment" . $Publi->getId()}->setAuteur($user);
                ${"newComment" . $Publi->getId()}->setCreatedAt(new \DateTime());
                ${"newComment" . $Publi->getId()}->setPublication($publicationCom);

                $manager->persist(${"newComment" . $Publi->getId()});
                $manager->flush();

                return $this->redirect($request->getUri());

            }


            $listCom = $repoComment->findBy(
                array('publication' => $Publi->getId()), // Critere
                array('createdAt' => 'desc')     // Tri
            );/* On recupère tout les commentaire de chaque publication et on les stock dans listCom
                    on stock la liste des commentaire de chaque publication dans le tableau
                       publiCom[id], id correspondant à l'identifiant de la publication donc on sait que les
                    commentaire de la publication 1 sont dans publiCom[1] */

            $publiCom[$Publi->getId()] = $listCom;



        }


        $repoFlows = $this->getDoctrine()->getRepository(Flow::class);

        $lastFlows = array();

        $userFriends = $user->getFriends();/* on recupère les users que l'utilisateur follow */

        foreach ($userFriends->toArray() as $friend){
            $flows = $manager->getRepository(Flow::class)->createQueryBuilder('o')
                ->where('o.createdAt > :date')
                ->setParameter('date', new \DateTime('-7 days'))
                ->andWhere('o.auteur = :id')
                ->setParameter('id', $friend)
                ->orderBy('o.createdAt', 'DESC')
                ->getQuery()
                ->getResult();

                $lastFlows = array_merge($lastFlows,$flows);
        }/* on recupere les derniers flow postés cette semaine des amis de l'utilisateur */

        //Permet de recuperer tout les flow et de les classe du plus recent au plus anciens
        usort($lastFlows, function($a, $b) {
            return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
        });


        $artisteFlow = array();
        $albumFlow = array();
        $playlistFlow = array();
        $trackFlow = array();

        foreach ($lastFlows as $flow){

            if($flow->getAlbumId() != NULL){
                array_push($albumFlow,$api->getAlbum($flow->getAlbumId()));
            }
            else if($flow->getArtisteId() != NULL){
                array_push($artisteFlow,$api->getArtist($flow->getArtisteId()));
            }
            else if($flow->getPlaylistId() != NULL){
                array_push($playlistFlow,$api->getPlaylist($flow->getPlaylistId()));
            }
            else if($flow->getTrackId() != NULL){
                array_push($trackFlow,$api->getTrack($flow->getTrackId()));
            }
        }
        /* ce Foreach petmet de classer les flows en fonctions de ce qu'ils concernent.
           si c'est un like d'artiste/album.. etc
           on stock chaque type de flow dans différents tableaux */

        $flowAll['track'] = $trackFlow;
        $flowAll['album'] = $albumFlow;
        $flowAll['artiste'] = $artisteFlow;
        $flowAll['playlist'] = $playlistFlow;

        return $this->render('reseau/filActualite.html.twig', [
            'publicationForm' => $formPublication->createView(),
            'publications' => $publication,
            'formsComment' => $formsComment,
            'publiCom' => $publiCom,
            'releases' => $releases,
            'recommendations' => $recommendations,
            'flowsAll' => $flowAll,
            'lastFlows' => $lastFlows
        ]);
    }

    /**
     * @Route("/reseau/{id}/delete", name="publication_delete")
     */
    public function deletePublication($id,Request $request,ObjectManager $manager){

        $publication = new Publication();

        $repo = $this->getDoctrine()->getRepository(Publication::class);

        $publication = $repo->findOneBy(['id' => $id]);

        $manager->remove($publication);
        $manager->flush();

        return $this->redirectToRoute('reseau');
    }


    /**
     * @Route("/reseau/{id}/deleteCom", name="comment_delete")
     */
    public function deleteComment($id,Request $request,ObjectManager $manager){

        $comment = new Comment();

        $repo = $this->getDoctrine()->getRepository(Comment::class);

        $comment = $repo->findOneBy(['id' => $id]);

        dump($comment);

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('reseau');

    }

    /**
     * @Route("/publication/{id}/comment/heart", name="flow_Comment", methods={"POST"}))
     */
    public function flowComment($id, LoggerInterface $logger,ObjectManager $manager){

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);
        $repocomment = $this->getDoctrine()->getRepository(Comment::class);

        $flow = new Flow();

        $user = $this->getUser();

        $comment = new Comment();
        $comment = $repocomment->findOneBy(['id' => $id]);/* On recupere le commentaire */

        /* On regarder si l'utilisateur a deja flow ce commentaire ou non */
        $exist = $repoFlow->findOneBy(['comment' => $id,'auteur' => $user->getId()]);

        /* s'il ne l'a pas flow alors on crée une nouveau Flow lié a ce commentaire et cet utilisateur */
        if(is_null($exist)){

            $flow->setComment($comment);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        /* Sinon ça veut dire que l'utilisateur veut enelever son flow donc on supprime le flow */
        else{

            $flowDelete = $repoFlow->findOneBy(['comment' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        /* Pour un futur systeme de flow négatifs, si le temps nous le permet */
        $flowsPositif = $repoFlow->findBy(array('comment' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('comment' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }


    /**
     * @Route("/publication/{id}/heart", name="flow_publication", methods={"POST"})
     */
    public function flowPublication($id, LoggerInterface $logger,ObjectManager $manager){

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);
        $repopublication = $this->getDoctrine()->getRepository(Publication::class);

        $flow = new Flow();

        $user = $this->getUser();

        $publication = new Publication();
        $publication = $repopublication->findOneBy(['id' => $id]);

        $exist = $repoFlow->findOneBy(['publication' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $flow->setPublication($publication);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{

            $flowDelete = $repoFlow->findOneBy(['publication' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('publication' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('publication' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }

    /**
     * @Route("/musique/{id}/heart", name="flow_musique", methods={"POST"})
     */
    public function flowMusique($id, LoggerInterface $logger,ObjectManager $manager){

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $exist = $repoFlow->findOneBy(['trackId' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $flow->setTrackId($id);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{

            $flowDelete = $repoFlow->findOneBy(['trackId' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('publication' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('publication' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }


    /**
     * @Route("/album/{id}/heart", name="flow_album", methods={"POST"})
     */
    public function flowAlbum($id, LoggerInterface $logger,ObjectManager $manager){

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $exist = $repoFlow->findOneBy(['albumId' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $flow->setAlbumId($id);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{

            $flowDelete = $repoFlow->findOneBy(['albumIdId' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('publication' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('publication' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }

    /**
     * @Route("/artiste/{id}/heart", name="flow_artiste", methods={"POST"})
     */
    public function flowArtiste($id, LoggerInterface $logger,ObjectManager $manager){

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $exist = $repoFlow->findOneBy(['artisteId' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $flow->setArtisteId($id);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{

            $flowDelete = $repoFlow->findOneBy(['artisteId' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('publication' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('publication' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }

    /**
     * @Route("/playlist/{id}/heart", name="flow_playlist", methods={"POST"})
     */
    public function flowPlaylist($id, LoggerInterface $logger,ObjectManager $manager,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $exist = $repoFlow->findOneBy(['playlistId' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $api->followPlaylistForCurrentUser($id);

            $flow->setPlaylistId($id);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{
            $api->unfollowPlaylistForCurrentUser($id);

            $flowDelete = $repoFlow->findOneBy(['playlistId' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('publication' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('publication' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }

    /**
     * @Route("/profil/{id}", name="profil")
     */
    public function profil($id,ObjectManager $manager,Request $request,SessionInterface $session){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $profilUser = new user();

        $repoUser = $this->getDoctrine()->getRepository(User::class);

        $profilUser = $repoUser->findOneBy(['id' => $id]);/* On recupere les information de l'user qui correspond a la page profil qu'on visite */

        $accessTokenProfilUser = $profilUser->getAccessToken();// On recupere les tokens du profil sur lequel on est
        $refreshTokenProfilUser = $profilUser->getRefreshToken();

        $sessionSpotifyProfil = new SpotifyController();// On crée un controller spotify ayant les id du profil sur lequel on est.

        $apiUser = $sessionSpotifyProfil->connectApiUser($accessTokenProfilUser,$refreshTokenProfilUser);

        $profilSpotify = $apiUser->me();// Ici le "me" est le profil spotify de l'utilisateur de notre page profil

        $userPlaylists = $apiUser->getUserPlaylists($profilSpotify->id);

        $user = $this->getUser();/* On recupere les informations de l'utilisateur connecté */

        if($profilUser != null && $user != null) {

            $user = new User();

            $user = $this->getUser();


            $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Publication::class);

            $listPubli = $repository->findBy(['auteur' => $profilUser]);

            $repoComment = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Comment::class);

            $formsComment = array();
            $publiCom = array();

            foreach ($listPubli as $Publi) {/* On recupere toutes les publication de l'utilisateur de notre profil */

                $idPubli[$Publi->getId()] = $Publi;

                ${"newComment" . $Publi->getId()} = new Comment();

                ${"form" . $Publi->getId()} = $this->createForm(CommentType::class, ${"newComment" . $Publi->getId()});


                ${"form" . $Publi->getId()} = $this->get('form.factory')->createNamed('form' . $Publi->getId(), CommentType::class, ${"newComment" . $Publi->getId()});

                ${"form" . $Publi->getId()}->handleRequest($request);


                $formsComment[$Publi->getId()] = ${"form" . $Publi->getId()}->createView();

                if (${"form" . $Publi->getId()}->isSubmitted() && ${"form" . $Publi->getId()}->isValid()) {

                    $user = new User();

                    $user = $this->getUser();

                    $publicationCom = $repo = $this->getDoctrine()->getRepository(Publication::class)->find($Publi->getId());


                    ${"newComment" . $Publi->getId()}->setAuteur($user);
                    ${"newComment" . $Publi->getId()}->setCreatedAt(new \DateTime());
                    ${"newComment" . $Publi->getId()}->setPublication($publicationCom);

                    $manager->persist(${"newComment" . $Publi->getId()});
                    $manager->flush();

                    return $this->redirect($request->getUri());

                }


                $listCom = $repoComment->findBy(
                    array('publication' => $Publi->getId()), // Critere
                    array('createdAt' => 'desc')     // Tri
                );

                $publiCom[$Publi->getId()] = $listCom;


            }

        }
        elseif ($user == null){

            return $this->render('notconnected.html.twig');
        }
        else{

            return $this->render('notfound.html.twig');

        }

        $repoFlows = $this->getDoctrine()->getRepository(Flow::class);

        $lastFlows = $repoFlows->findBy(array('auteur' => $profilUser->getId()),array('createdAt' => 'DESC'),20);

        $artisteFlow = array();
        $albumFlow = array();
        $playlistFlow = array();
        $trackFlow = array();

        foreach ($lastFlows as $flow){/* On recupere tout les flows de l'utilisateur du profil */

            if($flow->getAlbumId() != NULL){
                array_push($albumFlow,$api->getAlbum($flow->getAlbumId()));
            }
            else if($flow->getArtisteId() != NULL){
                array_push($artisteFlow,$api->getArtist($flow->getArtisteId()));
            }
            else if($flow->getPlaylistId() != NULL){
                array_push($playlistFlow,$api->getPlaylist($flow->getPlaylistId()));
            }
            else if($flow->getTrackId() != NULL){
                array_push($trackFlow,$api->getTrack($flow->getTrackId()));
            }
        }

        $flowAll['track'] = $trackFlow;
        $flowAll['album'] = $albumFlow;
        $flowAll['artiste'] = $artisteFlow;
        $flowAll['playlist'] = $playlistFlow;

        return $this->render('reseau/profil.html.twig', [
            'publications' => $listPubli,
            'formsComment' => $formsComment,
            'publiCom' => $publiCom,
            'name' => $profilUser->getUsername(),
            'profilUser' => $profilUser,
            'playlistsUser' => $userPlaylists,
            'flowsAll' => $flowAll,
            'lastFlows' => $lastFlows

        ]);
    }

    /**
     * @Route("/profil/{id}/playlist", name="profil_playlist", methods={"POST"})
     */
    /* Permet de gerer l'affichage des playlist d'un utilisateur sur sa page profil */
    public function profilPlaylist($id,ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlist = $api->getPlaylist($id);

        foreach( $playlist->images as $image ){
            if(!isset($img)){
                $img = '<img src="'.$image->url.'" width="400" height="400">';
                break;
            }
        }
        $name = 'Nom playlist : '.$playlist->name;
        $description = 'Description : '.$playlist->description;
        $follower = 'Followers : '.$playlist->followers->total;
        $embed = '<iframe src="https://open.spotify.com/embed/playlist/'. $playlist->id .'" width="300" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';

        return new JsonResponse([
            'namePlaylist' => $name,
            'descriptionPlaylist' => $description,
            'imgPlaylist' => $img,
            'followersPlaylist' => $follower,
            'embedPlaylist' => $embed
        ]);
    }


    /**
     * @Route("/playlist/{id}/track/{idtrack}/{idsnapchot}", name="track_playlist_delete")
     */
    /* Permet de supprimer un track d'une playlist */
    public function trackPlaylistDelete($id,$idtrack,$idsnapchot,ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $tracks = [
            'tracks' => [
                ['id' => $idtrack]
            ],
        ];

        $api->deletePlaylistTracks($id,$tracks,$idsnapchot);


        return $this->redirectToRoute('playlist_page',['id' => $id]);
    }

    /**
     * @Route("/playlist/track/{idtrack}", name="track_playlist_add")
     */
    public function trackPlaylistAdd($idtrack,ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlistId = $_POST['playlist'];

        $tracks = [
            'tracks' => [
                ['id' => $idtrack]
            ]
        ];

        $api->addPlaylistTracks($playlistId,$idtrack);


        return $this->redirectToRoute('playlist_page',['id' => $playlistId]);
    }


    /**
     * @Route("/playlist/{id}", name="playlist_page")
     */
    public function playlist($id,ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlist = $api->getPlaylist($id);

        $idCreateur = $playlist->owner->id;

        $repoUser = $this->getDoctrine()->getRepository(User::class);
        $createur = $repoUser->findOneBy(['idSpotify' => $idCreateur]);

        return $this->render('reseau/playlist.html.twig',[
            'playlist' => $playlist,
            'createur' => $createur
        ]);
    }

    /**
     * @Route("/profil/{id}/follow", name="profil_follow")
     */
    public function follow($id,ObjectManager $manager){


        $user= new User();

        $user = $this->getUser();

        $profilUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $id]);


        if($user->follow($profilUser)){

            $user->removeFriends($profilUser);

            $manager->persist($user);
            $manager->flush();

            return new JsonResponse(['status' => "Follow" ]);

        }
        else{

            $user->getFriends()->add($profilUser);

            $manager->persist($user);
            $manager->flush();

            return new JsonResponse(['status' => "Unfollow" ]);

        }
    }


    /**
     * @Route("/artiste/{id}", name="artiste_page")
     */
    public function artiste($id,ObjectManager $manager,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $artiste = $api->getArtist($id);

        $albumArtiste = $api->getArtistAlbums($id);

        $sonsArtiste = $api->getArtistTopTracks($id,['country' => "FR"]);

        return $this->render('reseau/artiste.html.twig',[
            'artiste' => $artiste,
            'albumArtiste' => $albumArtiste,
            'sonsArtiste' => $sonsArtiste
        ]);
    }

    /**
     * @Route("/musique/{id}", name="musique_page")
     */
    public function musique($id,ObjectManager $manager,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlistUser = $api->getUserPlaylists($api->me()->id);

        $track = $api->getTrack($id);

        dump($track);
        
        return $this->render('reseau/musique.html.twig',[
            'track' => $track,
            'playlistUser' => $playlistUser
        ]);
    }

    /**
     * @Route("/album/{id}", name="album_page")
     */
    public function album($id,ObjectManager $manager,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $album = $api->getAlbum($id);

        return $this->render('reseau/album.html.twig',[
            'album' => $album
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(SessionInterface $session, ObjectManager $manager)
    {
        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        if(isset($_POST['search'])){

            $rech = $_POST['search'];
            $result = $api->search($rech,['artist','album','track','playlist'],['limit'=>10]);
            /* On cherche les resultats spofity (artiste/ablum/track) de la recherche de l'utilisateur */

            $repoUser = $this->getDoctrine()->getRepository(User::class);

            /* On cherche si des Users de notre réseau social correspondent a la recherche de l'utilisateur */
            $resUser = $manager->getRepository(User::class)->createQueryBuilder('o')
                ->where('o.username LIKE :name')
                ->setParameter('name', '%'. $rech .'%')
                ->getQuery()
                ->getResult();
        }
        else{
            $result = "Vous n'avez pas saisi de caractères ";
        }

        return $this->render('reseau/search.html.twig',[
            'result' => $result,
            'resUser' => $resUser
        ]);
    }

    /**
     * @Route("/user/playlist", name="playlist_user")
     */
    /* cette fonction permet d'afficher les playlist de l'utilisateur */
    public function playlistUser(ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlistUser = $api->getUserPlaylists($api->me()->id);

        return $this->render('reseau/playlistUser.html.twig',[
            'playlistsUser' => $playlistUser
        ]);
    }

    /**
     * @Route("/search/playlist", name="playlist_search")
     */
    /* Recherche d'une playlist */
    public function playlistSearch(ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        if(isset($_POST['search'])){

            $rech = $_POST['search'];
            $result = $api->search($rech,['playlist'],['limit'=>10]);
            dump($result);
        }

        return $this->render('reseau/searchPlaylist.html.twig',[
            'result' => $result
        ]);
    }

    /**
     * @Route("/playlist/{id}/delete", name="playlist_delete")
     */
    public function playlistDelete($id,SessionInterface $session,ObjectManager $manager){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $api->unfollowPlaylistForCurrentUser($id);


        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $flowDelete = $repoFlow->findOneBy(['playlistId' => $id,'auteur' => $user->getId()]);

        $manager->remove($flowDelete);
        $manager->flush();

        return $this->redirectToRoute('playlist_user');
    }


    /**
     * @Route("/flows", name="flows")
     */
    /* Cette fonction permet d'afficher tout les flows de l'utilisateur connecté */
    public function flows(ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $user =$this->getUser();

        $flowUser = $repoFlow->findBy(['auteur' => $user]);

        $artisteFlow = array();
        $albumFlow = array();
        $playlistFlow = array();
        $trackFlow = array();

        foreach ($flowUser as $flow){

            if($flow->getAlbumId() != NULL){
                array_push($albumFlow,$api->getAlbum($flow->getAlbumId()));
            }
            else if($flow->getArtisteId() != NULL){
                array_push($artisteFlow,$api->getArtist($flow->getArtisteId()));
            }
            else if($flow->getTrackId() != NULL){
                array_push($trackFlow,$api->getTrack($flow->getTrackId()));
            }
        }

        $flowAll['track'] = $trackFlow;
        $flowAll['album'] = $albumFlow;
        $flowAll['artiste'] = $artisteFlow;

        dump($flowAll);

        return $this->render('reseau/flows.html.twig',[
            'flows' => $flowUser,
            'tabFlow' => $flowAll
        ]);
    }

    /**
     * @Route("/user/playlist/add", name="playlist_add")
     */
    public function newPlaylist(ObjectManager $manager,Request $request,SessionInterface $session){

        $sessionSpotify = new SpotifyController();

        $api = $sessionSpotify->connectApi($session);

        $playlist = $api->createPlaylist([
            'name' => $_POST['name']
        ]);

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flow = new Flow();

        $user = $this->getUser();

        $api->followPlaylistForCurrentUser($playlist->id);

        $flow->setPlaylistId($playlist->id);
        $flow->setAuteur($user);
        $flow->setType(1);
        $flow->setCreatedAt(new \DateTime());

        $manager->persist($flow);
        $manager->flush();

        return $this->redirectToRoute('playlist_page',['id' => $playlist->id]);
    }

}
