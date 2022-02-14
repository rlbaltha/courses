<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Description;
use App\Entity\Term;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/course")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findAllAsc(),
        ]);
    }

    /**
     * @Route("/area/{term}/{area}", name="course_area", methods={"GET"}, defaults={"term"="current","area"="U"})
     */
    public function area(CourseRepository $courseRepository, EntityManagerInterface $entityManager, $area, $term): Response
    {
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        if ($term =='current') {
            $term = $entityManager
                ->getRepository(Term::class)->findDefault();
            $term = $term->getTerm();
        }
        $termname = $entityManager
            ->getRepository(Term::class)->findName($term);
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findByArea($area, $term),
            'terms' => $terms,
            'termname' => $termname
        ]);
    }

    /**
     * @Route("/upper/{term}/{area}", name="course_upper", methods={"GET"}, defaults={"term"="current","area"="U"})
     */
    public function upper(CourseRepository $courseRepository, EntityManagerInterface $entityManager, $area, $term): Response
    {
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        if ($term =='current') {
            $term = $entityManager
                ->getRepository(Term::class)->findDefault();
            $term = $term->getTerm();
        }
        $termname = $entityManager
            ->getRepository(Term::class)->findName($term);
        return $this->render('course/area.html.twig', [
            'courses' => $courseRepository->findByArea($area, $term),
            'terms' => $terms,
            'termname' => $termname
        ]);
    }

    /**
     * @Route("/user", name="course_user", methods={"GET"})
     */
    public function byuser(CourseRepository $courseRepository, EntityManagerInterface $entityManager): Response
    {
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();

        $user = $this->getUser();
        $name = $user->getLastname().', '.$user->getFirstname();
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findByName($name),
            'terms' => $terms
        ]);
    }

    /**
     * @Route("/new", name="course_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('course_index');
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_show", methods={"GET"})
     */
    public function show(Course $course, EntityManagerInterface $entityManager): Response
    {
        $name = 'none';
        if ($this->get('security.authorization_checker')->isGranted('ROLE_EDITOR')) {
            $user = $this->getUser();
            $name = $user->getLastname().', '.$user->getFirstname();
        }
        $termname = $entityManager
            ->getRepository(Term::class)->findName($course->getTerm());
        $terms = $entityManager
            ->getRepository(Term::class)->findCurrent();
        $descriptions = $entityManager
            ->getRepository(Description::class)->findByTermcall($course->getTermcall());
        return $this->render('course/show.html.twig', [
            'descriptions' => $descriptions,
            'course' => $course,
            'terms' => $terms,
            'termname' => $termname,
            'name' => $name
        ]);
    }

    /**
     * @Route("/{id}/edit", name="course_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Course $course): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('course_index');
    }

}
