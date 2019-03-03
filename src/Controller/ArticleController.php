<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


use App\Entity\Admin;
use App\Entity\Article;

class ArticleController extends AbstractController{

    

    public function articles(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();

        for($i = 0; $i<sizeof($articles); $i++)  
            if( $articles[$i]->getImage() === null )
                $articles[$i]->setImage("default.png");

        $articles = array_reverse($articles);

        return $this->render('admin/pages/articles.html.twig', [
            'controller_name' => 'ArticleController',
            'user' => $user,
            'articles' => $articles
        ]);//*/
    }


    public function removeArticleImage(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find( $request->query->get('id') );      

        $this->forward('App\Controller\AdminController::removeImageFile', array(
            'imageName'  => $article->getImage(),
        ));

        $article->setImage(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response( "+" );
    }

    public function removeArticleImages(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find( $request->query->get('id') );      


        $ids = $request->query->get('ids');
        $images = $article->getImages();
        for($i = 0; $i<sizeof($ids); $i++){
            $this->forward('App\Controller\AdminController::removeImageFile', array(
                'imageName'  => $images[$ids[$i]],
            ));
            $images[$ids[$i]] = "";
        }

        $newImages = [];
        for($i = 0; $i<sizeof($images); $i++){
            if( $images[$i]!="" ) array_push( $newImages, $images[$i] ); 
        }
        
        $article->setImages( $newImages );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response( "+" );
    }

    public function newArticle(SessionInterface $session, Request $request, UserInterface $user){
        
        $article = new Article();
        $article->setTime(new \DateTime('now'));

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('link', TextType::class)
            ->add('image', FileType::class, array('required' => false))
            ->add('description', TextType::class, array('label' => 'Image description'))
            ->add('text', TextareaType::class)
            ->add('tags', TextType::class, array(
                'data' => '[]'
           ))
            ->add('author', ChoiceType::class, array(
                'choices'  => array(
                    $user->getName() => $user->getUsername(),
                    'Protests' => 'Protests',
                )))
            ->add('images', FileType::class, [
                    'multiple' => true,
                    'required' => false,
                    'attr'     => [
                        'accept' => 'image/*',
                        'multiple' => 'multiple'
                    ],
                    'label' => 'Gallery'
                ])
            ->add('published', ChoiceType::class, array(
                'choices'  => array('Publish' => 1, 'Hide' => 0)))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $article = $form->getData();
            
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            if( ( $form->get('image')->getData() ) === null ){
            } else {
                $file = $form->get('image')->getData();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $article->setImage($fileName);
            }

            $fileNames = [];
            $files = $form->get('images')->getData();
            foreach ($files as $file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                array_push( $fileNames, $fileName );
            }
            $article->setImages( $fileNames );

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminArticles');
        }

        return $this->render('admin/pages/newArticle.html.twig', [
            'controller_name' => 'ArticleController',
            'user' => $user,
            'form' => $form->createView()
        ]);//*/
    }

    public function editArticle(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);

        $article = $repository->find( $request->attributes->get('id') );

        $articleWithImages = clone $article;
 
        $article->setImage(null);
        $article->setImages(null);

        $authorChoices = array();
        if( $article->getAuthor() != 'Protests' && $article->getAuthor() != $user->getUsername() ){
            $user_repository = $this->getDoctrine()->getRepository(Admin::class);
            $admin = $user_repository->findOneBy(['username' => $article->getAuthor() ]);
            array_push( $authorChoices, [$admin->getName() => $article->getAuthor()] );
        }
        array_push( $authorChoices, [$user->getName() => $user->getUsername()] );
        array_push( $authorChoices, ['Protests' => 'Protests'] );

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('link', TextType::class)
            ->add('image', FileType::class, array('required' => false))
            ->add('description', TextType::class, array('label' => 'Image description'))
            ->add('text', TextareaType::class)
            ->add('tags', TextType::class, array(
                'data' => '[]'
           ))
            ->add('author', ChoiceType::class, array(
                'choices'  => $authorChoices))
            ->add('images', FileType::class, [
                    'multiple' => true,
                    'required' => false,
                    'label' => 'Add More Images',
                    'attr'     => [
                        'accept' => 'image/*',
                        'multiple' => 'multiple'
                    ]
                ])
            ->add('published', ChoiceType::class, array(
                'choices'  => array('Publish' => 1, 'Hide' => 0)))
            ->add('save', SubmitType::class, array('label' => 'Publish'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $article = $form->getData();
            
            
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            if( ( $form->get('image')->getData() ) === null ){
                $article->setImage( $articleWithImages->getImage() );
            } else {
                $this->forward('App\Controller\AdminController::removeImageFile', array(
                    'imageName'  => $articleWithImages->getImage(),
                ));
                $file = $form->get('image')->getData();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $article->setImage($fileName);
            }

            
            $fileNames = [];
            if( $articleWithImages->getImages() !== NULL ) $fileNames=$articleWithImages->getImages();
            $files = $form->get('images')->getData();
            foreach ($files as $file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                array_push( $fileNames, $fileName );
            }
            $article->setImages( $fileNames );

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminArticles');
        }

        return $this->render('admin/pages/editArticle.html.twig', [
            'controller_name' => 'ArticleController',
            'user' => $user,
            'article' => $article,
            'article_with_images' => $articleWithImages,
            'form' => $form->createView()
        ]);//*/
    }

    public function deleteArticle(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find( $request->attributes->get('id') );

        if( $article->getImage() !== null )
            $this->forward('App\Controller\AdminController::removeImageFile', array(
                'imageName'  => $article->getImage(),
            ));
        
        if( $article->getImages() !== null ){
            $images = $article->getImages();
            foreach( $images as $image ){
                $this->forward('App\Controller\AdminController::removeImageFile', array(
                    'imageName'  => $image,
                ));
            }
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();
        return $this->redirectToRoute('adminArticles');
    } 

    public function visibilityArticle(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find( $request->query->get('id') );

        if(  $request->query->get('action') == 0 ||  $request->query->get('action') == 1 )
            $article->setPublished( $request->query->get('action') );
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();
        return $this->redirectToRoute('adminArticles');
    } 

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


}