<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Term;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'terms' => $terms
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/home", name="user_home", methods={"GET"})
     */
    public function home(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        if ($user->getLastname() == '') {
            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }
        $name = $user->getLastname().', '.$user->getFirstname();
        $courses = $entityManager
            ->getRepository(Course::class)->findByName($name);
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        return $this->render('user/home.html.twig', [
            'user' => $user,
            'courses' => $courses,
            'terms' => $terms
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'terms' => $terms
        ]);
    }



    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

//    /**
//     * Promote Users
//     *
//     * @Route("/{username}/{role}/promote", name="user_promote")
//     */
//    public function promoteuserAction($username,$role)
//    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//        $userManager = $this->container->get('')
//        $user = $userManager->findUserByUsername($username);
//        $user->addRole($role);
//        $userManager->updateUser($user);
//        return $this->redirectToRoute('user_index');
//    }
//
//    /**
//     * Demote Users
//     *
//     * @Route("/{username}/{role}/demote", name="user_demote")
//     */
//    public function demoteuserAction($username,$role)
//    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
//        $userManager = $this->container->get('fos_user.user_manager');
//        $user = $userManager->findUserByUsername($username);
//        $user->removeRole($role);
//        $userManager->updateUser($user);
//        return $this->redirectToRoute('user_index');
//    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
