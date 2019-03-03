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
use App\Entity\Partner;

class PartnerController extends AbstractController{

    public function partners(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Partner::class);
        $partners = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        if( count($partners)==0 ) $partners="NO";

        return $this->render('admin/pages/partners.html.twig', [
            'controller_name' => 'PartnerController',
            'user' => $user,
            'partners' => $partners
        ]);//*/
    }


    public function newPartner(SessionInterface $session, Request $request, UserInterface $user){

        $partner = new Partner();

        $partner->setPosition(0);
        $partner->setLink("");

        $form = $this->createFormBuilder($partner)
            ->add('name', TextType::class)
            ->add('logo', FileType::class)
            ->add('link', TextType::class, array('required' => false, 'empty_data' => ''))
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $partner = $form->getData();

            $file = $form->get('logo')->getData();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $partner->setLogo($fileName);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($partner);
            $entityManager->flush();
            
            $repository = $this->getDoctrine()->getRepository(Partner::class);
            $partn = $repository->findOneBy( ['name' => $partner->getName()] );
            $partn->setPosition( $partn->getId() );

            $entityManager->persist($partn);
            $entityManager->flush();

            return $this->redirectToRoute('adminPartners', array('message' => 'partner-created'));
        }
//*/
        return $this->render('admin/pages/newPartner.html.twig', [
            'controller_name' => 'PartnerController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function editPartner(SessionInterface $session, Request $request, UserInterface $user){

        $repository = $this->getDoctrine()->getRepository(Partner::class);

        $partner = $repository->find( $request->attributes->get('id') );

        $partnerWithFile = clone $partner;
 
        $partner->setLogo(null);

        $form = $this->createFormBuilder($partner)
            ->add('name', TextType::class)
            ->add('logo', FileType::class, array('required' => false))
            ->add('link', TextType::class, array('required' => false, 'empty_data' => ''))
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
            
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $partner = $form->getData();
            
            
            if( ( $form->get('logo')->getData() ) === null ){
                $partner->setLogo( $partnerWithFile->getLogo() );
            } else {
                $this->forward('App\Controller\AdminController::removeFile', array(
                    'fileName'  => $partnerWithFile->getLogo(),
                ));
                $file = $form->get('logo')->getData();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $partner->setLogo($fileName);
            }


            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($partner);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminPartners');
        }

        //*/
        return $this->render('admin/pages/editPartner.html.twig', [
            'controller_name' => 'PartnerController',
            'user' => $user,
            'partner' => $partner,
            'partner_with_file' => $partnerWithFile,
            'form' => $form->createView()
        ]);
    }

    public function swapPartner(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Partner::class);
        $partner0 = $repository->find( $request->query->get('id0') );
        $partner1 = $repository->find( $request->query->get('id1') );

        $pos0 = $partner0->getPosition();
        $pos1 = $partner1->getPosition();

        $partner0->setPosition($pos1);
        $partner1->setPosition($pos0);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($partner0);
        $entityManager->persist($partner1);
        $entityManager->flush();
        return $this->redirectToRoute('adminPartners');
    } 

    public function deletePartner(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Partner::class);
        $partner = $repository->find( $request->attributes->get('id') );

        if( $partner->getLogo() !== null )
            $this->forward('App\Controller\AdminController::removeFile', array(
                'fileName'  => $partner->getLogo(),
            ));
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($partner);
        $entityManager->flush();
        return $this->redirectToRoute('adminPartners');
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