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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


use App\Entity\Admin;
use App\Entity\Content;

class ContentController extends AbstractController{
    
    public function getContentCats(){
        $cats = ['Not in use', 'Menu', 'Footer', 'News', 'Landing page', 'Documents', 'Join', 'Calendar', 'About us', 'Contact us', 'Donate', 'Partners', 'Privacy'];
        return $cats;
    }

    public function contents(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Content::class);

        $cats = $this->getContentCats();

        $contents = array( array() );
        for($i = 0; $i<sizeof($cats); $i++){
            $contents[$i] = $repository->findBy( ['category' => $i] );
        }

        return $this->render('admin/pages/contents.html.twig', [
            'controller_name' => 'ContentController',
            'user' => $user,
            'contents' => $contents,
            'cats' => $cats
        ]);//*/
    }


    public function newContent(SessionInterface $session, Request $request, UserInterface $user){

        $content = new Content();

        $cats = $this->getContentCats();
        $catList = array();
        for($i = 0; $i<sizeof($cats); $i++){
            $catList[ $cats[$i] ] = $i;  
        }


        $form = $this->createFormBuilder($content)
            ->add('category', ChoiceType::class, array(
                'choices'  => $catList))
            ->add('location', TextType::class)
            ->add('content', TextareaType::class, array('label' => 'Content (lv)'))
            ->add('contenten', TextareaType::class, array('label' => 'Content (en)'))
            ->add('contentru', TextareaType::class, array('label' => 'Content (ru)'))
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $content = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('adminContents');
        }
//*/
        return $this->render('admin/pages/newContent.html.twig', [
            'controller_name' => 'ContentController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function editContent(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Content::class);

        $content = $repository->find( $request->attributes->get('id') );

        $cats = $this->getContentCats();
        $catList = array();
        for($i = 0; $i<sizeof($cats); $i++){
            $catList[ $cats[$i] ] = $i;  
        }
 
        $form = $this->createFormBuilder($content)
            ->add('category', ChoiceType::class, array(
                'choices'  => $catList))
            ->add('location', TextType::class)
            ->add('content', TextareaType::class, array('label' => 'Content (lv)'))
            ->add('contenten', TextareaType::class, array('label' => 'Content (en)'))
            ->add('contentru', TextareaType::class, array('label' => 'Content (ru)'))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $content = $form->getData();
    
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($content);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminContents');
        }

        //*/
        return $this->render('admin/pages/editContent.html.twig', [
            'controller_name' => 'ContentController',
            'user' => $user,
            'content' => $content,
            'form' => $form->createView()
        ]);
    }

    public function deleteDocument(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Document::class);
        $document = $repository->find( $request->attributes->get('id') );

        if( $document->getFile() !== null )
            $this->forward('App\Controller\AdminController::removeFile', array(
                'fileName'  => $document->getFile(),
            ));
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($document);
        $entityManager->flush();
        return $this->redirectToRoute('adminDocuments');
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