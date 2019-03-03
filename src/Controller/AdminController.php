<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Entity\Admin;
use App\Entity\Article;
use App\Entity\Event;
use App\Entity\Document;

class AdminController extends AbstractController{

    private function checkIfLoggedIn(SessionInterface $session){
        if( $session->get('adminId', -1) == -1 ) return false; else return true;
    }

    private function getAdminById($id){
        $repository = $this->getDoctrine()->getRepository(Admin::class);
        $admin = $repository->find($id);
        return $admin;
    }

    public function removeFile($fileName){
        if( $fileName=="" || $fileName===null ) return false;
        $fileSystem = new Filesystem();

        $deletable = $this->getParameter('uploads_directory').$fileName;

        if( $fileSystem->exists( $deletable ) ){
            try {
                $fileSystem->remove( $deletable );
            } catch (IOExceptionInterface $exception) {
                return new Response( "An error occurred while deleting the image at ".$exception->getPath() );
            }
        } else {
            return new Response( "This file does not exist: ".$deletable );
        }
        return true;
    }


    public function index(SessionInterface $session, UserInterface $user){

        $article_repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $article_repository->findAll();

        for($i = 0; $i<sizeof($articles); $i++){
            if( $articles[$i]->getImage() === null )
                $articles[$i]->setImage("default.png");
        }

        $articles = array_reverse($articles);

        $event_repository = $this->getDoctrine()->getRepository(Event::class);
        $events = $event_repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        $statuses = array(false, false, false);

        for($i = 0; $i<sizeof($events); $i++){
            $statuses[ $events[$i]->getFinished() ] = true;
        }

        $document_repository = $this->getDoctrine()->getRepository(Document::class);
        $documents = $document_repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        return $this->render('admin/pages/index.html.twig', [
            'controller_name' => 'AdminController',
            'articles' => $articles,
            'events' => $events,
            'documents' => $documents,
            'upcoming' => $statuses[0],
            'ongoing' => $statuses[1],
            'finished' => $statuses[2],
            'user' => $user
        ]);//*/
    }


    public function myAccount(SessionInterface $session, Request $request, UserInterface $user, UserPasswordEncoderInterface $encoder){
        
        $defaultData = array('name' =>  $user->getName());

        $form = $this->createFormBuilder($defaultData)
            ->add('name', TextType::class, array('label' => 'Display name'))
            ->add('currentPassword', PasswordType::class, array('label' => 'Approve changes with your (current) password'))
            ->add('password', RepeatedType::class,array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'New Password'),
                'second_options' => array('label' => 'Repeat New Password'),
            ))
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();

            if (!$encoder->isPasswordValid($user, $data['currentPassword'])) {
                $this->addFlash('danger', 'Current password is invalid. Please try again');
                return $this->redirectToRoute('adminMyAccount', array('message' => 'wrong-password') );
            }

            $user->setName( $data['name'] ); 
            if( $data['password'] !== null && $data['password'] != "" ){
                
                $pass = $encoder->encodePassword($user, $data['password']);
                $user->setPassword( $pass );    
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('adminMyAccount', array('message' => 'success') );
        }

        return $this->render('admin/pages/myAccount.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
            'form' => $form->createView(),
            'message' => $request->query->get('message')
        ]);//*/
    }

}