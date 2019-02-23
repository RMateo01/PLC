<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Flow;
use App\Entity\Publication;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = \Faker\Factory::create('fr_FR');

        //On crée un utilisateur qui existe toujours pour faire des tests.

        $user = new User();
        $user->setUsername('Romain');
        $user->setPassword('$2y$10$00Jzhe7i9NKH.4LVCKz8N.PoOe6dizCRLpS0ZWZD7DN2zXOmFghlG');
        $user->setEmail('Romain@gmail.com');

        $manager->persist($user);

        //Créer 3 Categories fake et 3 Fake user

        for($i=0;$i<3;$i++){

            $user = new User();
            $user->setUsername($faker->userName());
            $user->setPassword('$2y$10$00Jzhe7i9NKH.4LVCKz8N.PoOe6dizCRLpS0ZWZD7DN2zXOmFghlG');
            $user->setEmail($faker->email());

            $accessToken = 'BQBRkh6_xJidHx-IYvs2dh3lrWSkr-TIMUzT_b5UcXbzESsjc9LJjwWtLzmGlX08AUFPj1YAP6ALAE5em8jyzdce6Zcd8CPC9SENA2ylfYNOXM4eTO_NCHGJkPHcKDxjTUi2HoYyEtpdw90CTKI16-Xvct_Ea_XbPlorvsIS53hjIj92V3MRhCaP1ScD6K0Rf4MRI7Gh9SqIwUlLEMzpyKQDPaCdPZLFqB504aM3lQ7ZxQeRZwxm2SKvhLE5eo1LUwiA4HLPkeDijWjSPFgDPUof9jiB-Cg';
            $refreshToken = 'AQAjc9amwZQtOT3jDGrVUN6j7GXmLUgAWYCB8S4X2BklMUJ8QStVdRDANxND8nomR_uQPZdCMz8Yw83Mpy13rzyvCvGJ-ep-iUQBuB9BSWV-McaMFjTWwcCK2s5Xm4K5iHvnLQ';

            $user->setRefreshToken($refreshToken);
            $user->setAccessToken($accessToken);
            /* On attribue notre compte neutre Spotify aux comptes fake pour eviter des erreurs. */

            $manager->persist($user);


            //On crée une publication pour chaque utilisateur
            $newPublication = new Publication();

            $newPublication->setAuteur($user);
            $newPublication->setDateCreation(new \DateTime());
            $newPublication->setContenu('Publication de '.$user->getUsername() .'');

            $manager->persist($newPublication);

            //On ajoute un flow de cette publication à l'utilisateur
            $flow = new Flow();

            $flow->setPublication($newPublication);
            $flow->setAuteur($user);
            $flow->setType(1);
            $flow->setCreatedAt(new \DateTime());

            $manager->persist($flow);

            $flowArtiste = new Flow();
            $flowArtiste->setArtisteId("4FpJcNgOvIpSBeJgRg3OfN");
            $flowArtiste->setAuteur($user);
            $flowArtiste->setType(1);
            $flowArtiste->setCreatedAt(new \DateTime());

            $manager->persist($flowArtiste);


            $flowAlbum = new Flow();
            $flowAlbum->setAlbumId("5GfsaNstrK8rszTX5XYtXU");
            $flowAlbum->setAuteur($user);
            $flowAlbum->setType(1);
            $flowAlbum->setCreatedAt(new \DateTime());

            $manager->persist($flowAlbum);

            



            $category = new Category();
            $category->setTitle($faker->sentence())
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            //Creer entre 4 et 6 articles

            /*
            $content = '<p>';
            $content .= join($faker->paragraphs(5),'</p><p>');
            $content .= '</p>';
            */



            for($j=1;$j <= mt_rand(4,6);$j++){



                $content = '<p>' . join($faker->paragraphs(5),'</p><p>') . '</p>';

                $article = new Article();
                $article->setTitle($faker->sentence());
                $article->setContent($content);
                $article->setImage($faker->imageUrl());
                $article->setCreatedAt($faker->dateTimeBetween('-6 months'));
                $article->setCategory($category);
                $article->setAuthor($user);


                $manager->persist($article);

                for($k=1;$k<mt_rand(4,10);$k++){

                    $content = '<p>' . join($faker->paragraphs(2),'</p><p>') . '</p>';

                    $now = new \DateTime();

                    $interval = $now->diff($article->getCreatedAt());
                    $days = $interval->days;

                    $minimum = '-' . $days . ' days';

                    $comment = new Comment();
                    $comment->setAuteur($user)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween($minimum))
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }


        }

        $manager->flush();
    }
}
