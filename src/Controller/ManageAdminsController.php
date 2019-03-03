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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\Admin;
use App\Entity\Article;

class ManageAdminsController extends AbstractController{

    public function switchRoles(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Admin::class);
        $admin = $repository->find( $request->query->get('id') );
        $role = $request->query->get('role');
        $roles = $admin->getRoles();
        $newRoles = [];
        $deleted = false;
        foreach( $roles as $r ){
            if( $r == $role ){
                $deleted = true;
            } else {
                array_push( $newRoles, $r );
            }
        }

        if( $deleted == false ) array_push( $newRoles, $role );

        $admin->setRoles($newRoles);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($admin);
        $entityManager->flush();

        return new Response( "+" );
    }

    public function admins(SessionInterface $session, Request $request, UserInterface $user){
        
        $repository = $this->getDoctrine()->getRepository(Admin::class);
        $admins = $repository->findAll();

        $roleTable = array(array());

        foreach( $admins as $admin ){
            $adminRoles = $admin->getRoles();
            $adminNr = $admin->getId();
            $roleTable[$adminNr]['ROLE_SUPERADMIN'] = $roleTable[$adminNr]['ROLE_ADMIN'] = $roleTable[$adminNr]['ROLE_EDITOR'] = false;
            for($i = 0; $i<sizeof($adminRoles); $i++){
                if( $adminRoles[$i]=='ROLE_ADMIN' ){
                    $roleTable[$adminNr]['ROLE_ADMIN'] = true;
                }
                if( $adminRoles[$i]=='ROLE_EDITOR' ){
                    $roleTable[$adminNr]['ROLE_EDITOR'] = true;
                }
                if( $adminRoles[$i]=='ROLE_SUPERADMIN' ){
                    $roleTable[$adminNr]['ROLE_SUPERADMIN'] = true;
                }
            }
        }

        return $this->render('admin/pages/manageAdmins.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
            'admins' => $admins,
            'roles' => $roleTable,
            'message' => $request->query->get('message')
        ]);//*/
    }

    public function newAdmin(SessionInterface $session, Request $request, UserInterface $user, UserPasswordEncoderInterface $encoder){
        
        $admin = new Admin();

        $form = $this->createFormBuilder($admin)
            ->add('name', TextType::class, array('label' => 'Display Name'))
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class,array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('save', SubmitType::class, array('label' => 'Create'))
            ->getForm();
        
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $admin = $form->getData();

            $admin->setPassword( $encoder->encodePassword($admin, $admin->getPassword() ) );
            
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($admin);
            $entityManager->flush();
    
            return $this->redirectToRoute('adminManageAdmins', array('message' => 'admin-created') );
        }

        return $this->render('admin/pages/newAdmin.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
            'form' => $form->createView()
        ]);//*/
    }

    public function deleteAdmin(SessionInterface $session, Request $request, UserInterface $user){
        $repository = $this->getDoctrine()->getRepository(Admin::class);
        $admin = $repository->find( $request->attributes->get('id') );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($admin);
        $entityManager->flush();
        return $this->redirectToRoute('adminManageAdmins', array('message' => 'admin-removed') );
    } 


}