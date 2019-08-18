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


use App\Entity\Admin;
use App\Entity\Subscriber;

class EmailController extends AbstractController{   

    public function events(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $events = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        for($i = 0; $i<sizeof($events); $i++){
            $events[$i]->setDescription( strip_tags($events[$i]->getDescription()) );
        }

        return $this->render('admin/pages/events.html.twig', [
            'controller_name' => 'EventController',
            'user' => $user,
            'events' => $events
        ]);//*/
    }


    public function newEvent(SessionInterface $session, Request $request, UserInterface $user){

        $event = new Event();

        $event->setPosition(0);

        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class)
            ->add('time0', TextType::class, array('label' => 'Date/Time (line 1)'))
            ->add('time1', TextType::class, array('label' => 'Date/Time (line 2)'))
            ->add('location', TextType::class)
            ->add('description', TextareaType::class)
            ->add('finished', ChoiceType::class, array(
                'choices'  => array(
                    'Upcomming' => 0, 'Ongoing' => 1, 'Finished' => 2
                )))
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $event = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
            
            $repository = $this->getDoctrine()->getRepository(Event::class);
            $ev = $repository->findOneBy( ['title' => $event->getTitle(), 'time0' => $event->getTime0()] );
            $ev->setPosition( $ev->getId() );

            $entityManager->persist($ev);
            $entityManager->flush();

            return $this->redirectToRoute('adminEvents', array('message' => 'event-created'));
        }
//*/
        return $this->render('admin/pages/newEvent.html.twig', [
            'controller_name' => 'DocumentController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function editEvent(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);

        $event = $repository->find( $request->attributes->get('id') );

        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class)
            ->add('time0', TextType::class, array('label' => 'Date/Time (line 1)'))
            ->add('time1', TextType::class, array('label' => 'Date/Time (line 2)'))
            ->add('location', TextType::class)
            ->add('description', TextareaType::class)
            ->add('finished', ChoiceType::class, array(
                'choices'  => array(
                    'Upcomming' => 0, 'Ongoing' => 1, 'Finished' => 2
                )))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $event = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminEvents');
        }

        //*/
        return $this->render('admin/pages/editEvent.html.twig', [
            'controller_name' => 'EventController',
            'user' => $user,
            'event' => $event,
            'form' => $form->createView()
        ]);
    }

    public function swapEvent(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $event0 = $repository->find( $request->query->get('id0') );
        $event1 = $repository->find( $request->query->get('id1') );

        $pos0 = $event0->getPosition();
        $pos1 = $event1->getPosition();

        $event0->setPosition($pos1);
        $event1->setPosition($pos0);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($event0);
        $entityManager->persist($event1);
        $entityManager->flush();
        return $this->redirectToRoute('adminEvents');
        //return new Response($pos0." ".$pos1);
    } 

    public function deleteEvent(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $event = $repository->find( $request->attributes->get('id') );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($event);
        $entityManager->flush();
        return $this->redirectToRoute('adminEvents');
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