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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


use App\Entity\Admin;
use App\Entity\Person;

class PeopleController extends AbstractController{

    public function people(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $people = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        if( count($people)==0 ) $people="NO";

        return $this->render('admin/pages/people.html.twig', [
            'controller_name' => 'PeopleController',
            'user' => $user,
            'people' => $people
        ]);//*/
    }


    public function newPerson(SessionInterface $session, Request $request, UserInterface $user){

        $person = new Person();

        $person->setPosition(0);
        $person->setImage(null);

        $form = $this->createFormBuilder($person)
            ->add('name', TextType::class)
            ->add('image', FileType::class)
            ->add('post', TextType::class)
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $person = $form->getData();

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
            $person->setImage($fileName);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($person);
            $entityManager->flush();
            
            $repository = $this->getDoctrine()->getRepository(Person::class);
            $pers = $repository->findOneBy( ['name' => $person->getName()] );
            $pers->setPosition( $pers->getId() );

            $entityManager->persist($pers);
            $entityManager->flush();

            return $this->redirectToRoute('adminPeople', array('message' => 'person-created'));
        }
//*/
        return $this->render('admin/pages/newPerson.html.twig', [
            'controller_name' => 'PeopleController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function editPerson(SessionInterface $session, Request $request, UserInterface $user){

        $repository = $this->getDoctrine()->getRepository(Person::class);

        $person = $repository->find( $request->attributes->get('id') );

        $personWithFile = clone $person;
 
        $person->setImage(null);

        $form = $this->createFormBuilder($person)
            ->add('name', TextType::class)
            ->add('image', FileType::class, array('required' => false))
            ->add('post', TextType::class)
            ->add('description', TextareaType::class, array('label' => 'Description'))
            ->add('posten', TextType::class, array('label' => 'Post (English)'))
            ->add('descriptionen', TextareaType::class, array('label' => 'Description (English)'))
            ->add('postru', TextType::class, array('label' => 'Post (Russian)'))
            ->add('descriptionru', TextareaType::class, array('label' => 'Description (Russian)'))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
            
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $person = $form->getData();
            
            
            if( ( $form->get('image')->getData() ) === null ){
                $person->setImage( $personWithFile->getImage() );
            } else {
                $this->forward('App\Controller\AdminController::removeFile', array(
                    'fileName'  => $personWithFile->getImage(),
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
                $person->setImage($fileName);
            }


            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($person);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminPeople');
        }

        //*/
        return $this->render('admin/pages/editPerson.html.twig', [
            'controller_name' => 'PeopleController',
            'user' => $user,
            'person' => $person,
            'person_with_file' => $personWithFile,
            'form' => $form->createView()
        ]);
    }

    public function swapPeople(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $person0 = $repository->find( $request->query->get('id0') );
        $person1 = $repository->find( $request->query->get('id1') );

        $pos0 = $person0->getPosition();
        $pos1 = $person1->getPosition();

        $person0->setPosition($pos1);
        $person1->setPosition($pos0);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($person0);
        $entityManager->persist($person1);
        $entityManager->flush();
        return $this->redirectToRoute('adminPeople');
    } 

    public function deletePerson(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $person = $repository->find( $request->attributes->get('id') );

        if( $person->getImage() !== null )
            $this->forward('App\Controller\AdminController::removeFile', array(
                'fileName'  => $person->getImage(),
            ));
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($person);
        $entityManager->flush();
        return $this->redirectToRoute('adminPeople');
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