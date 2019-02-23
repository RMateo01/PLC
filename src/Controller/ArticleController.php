<?php
/**
 * Created by PhpStorm.
 * User: ROMAIN
 * Date: 10/10/2018
 * Time: 11:26
 */

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Flow;
use App\Entity\User;
use App\Form\ArticleType;
use App\Form\CommentType;
use Doctrine\Common\Persistence\ObjectManager;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Michelf\MarkdownInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage()
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);

        $articles = $repo->findAll();

        return $this->render('article/homepage.html.twig', [
            'articles' => $articles,
            ]);
    }

    /**
     * @Route("/news", name="news")
     */
    public function news(SessionInterface $session)
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);

        $articles = $repo->findAll();

        $sessionSpotify = new SpotifyController();
        
        if( null!==$session->get('accessToken') && null!==$session->get('refreshToken')){
            $api = $sessionSpotify->connectApi($session);
        }
        else{
            $api = $sessionSpotify->connectApiNeutral();
        }

        $releases = $api->getNewReleases([
            'limit' => 10
        ]);

        return $this->render('article/news.html.twig', [
                'articles' => $articles,
                'releases' => $releases
        ]);
    }


    /**
     * @Route("/news/new", name="creer_article"))
     * @Route("/news/{id}/edit", name="editer_article")
     */
    public function form(Article $article = null,Request $request, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$article){
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class,$article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = new User();
            $user = $this->getUser();

            if(!($article->getId())){
                $article->setCreatedAt(new \DateTime());
                $article->setAuthor($user);
            }

            $manager->persist($article);
            $manager->flush();

            //return $this->redirectToRoute('article_show',['id'=> $article->getId()]);
            return $this->redirect($request->getUri());
        }

        return $this->render('article/create.html.twig',[
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);

    }

    /**
     * @Route("/news/{id}", name="article_show")
     */
    public function show($id, MarkdownInterface $markdown, AdapterInterface $cache,Request $request,ObjectManager $manager)
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);

        $article = $repo->find($id);

        $user = new User();
        $user = $this->getUser();

        $comment = new Comment();

        $form = $this->createForm(CommentType::class,$comment);

        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setAuteur($this->getUser());
            $comment ->setCreatedAt(new \DateTime());
            $comment ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();

            //$this->redirectToRoute('article_show',['id' => $article->getId()]);
            return $this->redirect($request->getUri());
        }

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);

        $flowsPositif = $repoFlow->findBy(array('article' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('article' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;

        return $this->render('article/show.html.twig',[
            'article' => $article,
            'id' => $id,
            'commentForm' => $form->createView(),
            'flows' => $nbflows
        ]);
    }



    /**
     * @Route("/news/{id}/heart", name="article_toggle_heart", methods={"POST"})
     */
    public function toggleArticleHeart($id, LoggerInterface $logger,ObjectManager $manager){

        // TODO - actually heart/unheart the article

        $repoFlow = $this->getDoctrine()->getRepository(Flow::class);
        $repoArticle = $this->getDoctrine()->getRepository(Article::class);

        $flow = new Flow();

        $user = $this->getUser();

        $article = new Article();
        $article = $repoArticle->findOneBy(['id' => $id]);

        $exist = $repoFlow->findOneBy(['article' => $id,'auteur' => $user->getId()]);

        if(is_null($exist)){

            $flow->setArticle($article);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);
            $manager->flush();

        }
        else{

            $flowDelete = $repoFlow->findOneBy(['article' => $id,'auteur' => $user->getId()]);

            $manager->remove($flowDelete);
            $manager->flush();

        }

        $flowsPositif = $repoFlow->findBy(array('article' => $id,'type' => 1));
        $flowsNegatif = $repoFlow->findBy(array('article' => $id,'type' => 0));

        $nbflowsP = count($flowsPositif);
        $nbflowsN = count($flowsNegatif);
        $nbflows = $nbflowsP-$nbflowsN;


        return new JsonResponse(['flows' => $nbflows]);

    }



}