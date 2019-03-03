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
use App\Entity\Document;

class DocumentController extends AbstractController{   
    
    public function getDocumentCats(){
        $cats = ['Main documents', 'Meeting summaries'];
        return $cats;
    }

    public function documents(SessionInterface $session, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Document::class);
        $documents = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        return $this->render('admin/pages/documents.html.twig', [
            'controller_name' => 'DocumentController',
            'user' => $user,
            'categories' => $this->getDocumentCats(),
            'documents' => $documents
        ]);//*/
    }


    public function newDocument(SessionInterface $session, Request $request, UserInterface $user){

        $cats = $this->getDocumentCats();
        $catList = array();
        for($i = 0; $i<sizeof($cats); $i++){
            $catList[ $cats[$i] ] = $i;  
        }

        $document = new Document();

        $document->setPosition(0);

        $form = $this->createFormBuilder($document)
            ->add('type', ChoiceType::class, array(
                'choices'  => $catList))
            ->add('name', TextType::class)
            ->add('file', FileType::class)
            ->add('icon', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $document = $form->getData();

            $file = $form->get('file')->getData();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $document->setFile($fileName);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
            
            $repository = $this->getDoctrine()->getRepository(Document::class);
            $doc = $repository->findOneBy( ['name' => $document->getName()] );
            $doc->setPosition( $doc->getId() );

            $entityManager->persist($doc);
            $entityManager->flush();

            return $this->redirectToRoute('adminDocuments', array('message' => 'document-created'));
        }
//*/
        return $this->render('admin/pages/newDocument.html.twig', [
            'controller_name' => 'DocumentController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function editDocument(SessionInterface $session, Request $request, UserInterface $user){
        
        $cats = $this->getDocumentCats();
        $catList = array();
        for($i = 0; $i<sizeof($cats); $i++){
            $catList[ $cats[$i] ] = $i;  
        }


        $repository = $this->getDoctrine()->getRepository(Document::class);

        $document = $repository->find( $request->attributes->get('id') );

        $documentWithFile = clone $document;
 
        $document->setFile(null);

        $form = $this->createFormBuilder($document)
            ->add('type', ChoiceType::class, array(
                'choices'  => $catList))
            ->add('name', TextType::class)
            ->add('file', FileType::class, array('required' => false))
            ->add('icon', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $document = $form->getData();
            
            
            if( ( $form->get('file')->getData() ) === null ){
                $document->setFile( $documentWithFile->getFile() );
            } else {
                $this->forward('App\Controller\AdminController::removeFile', array(
                    'fileName'  => $documentWithFile->getFile(),
                ));
                $file = $form->get('file')->getData();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $document->setFile($fileName);
            }


            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminDocuments');
        }

        //*/
        return $this->render('admin/pages/editDocument.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
            'document' => $document,
            'document_with_file' => $documentWithFile,
            'form' => $form->createView()
        ]);
    }

    public function swapDocument(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Document::class);
        $document0 = $repository->find( $request->query->get('id0') );
        $document1 = $repository->find( $request->query->get('id1') );

        $pos0 = $document0->getPosition();
        $pos1 = $document1->getPosition();

        $document0->setPosition($pos1);
        $document1->setPosition($pos0);
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($document0);
        $entityManager->persist($document1);
        $entityManager->flush();
        return $this->redirectToRoute('adminDocuments');
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