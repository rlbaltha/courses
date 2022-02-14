<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\Description;
use App\Form\DescriptionType;
use App\Repository\DescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/description")
 */
class DescriptionController extends AbstractController
{
    /**
     * @Route("/", name="description_index", methods={"GET"})
     */
    public function index(DescriptionRepository $descriptionRepository): Response
    {
        return $this->render('description/index.html.twig', [
            'descriptions' => $descriptionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/byuser", name="description_user", methods={"GET"})
     */
    public function byuser(DescriptionRepository $descriptionRepository): Response
    {
        $user = $this->getUser();
        return $this->render('description/index.html.twig', [
            'descriptions' => $descriptionRepository->findByUser($user),
        ]);

    }

    /**
     * @Route("{course}/new", name="description_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, $course): Response
    {
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user->getUserIdentifier());
        $course = $entityManager->getRepository(Course::class)->find($course);
        $description = new Description();
        $description->setTermcall($course->getTermcall());
        $description->setCourse($course->getTitle());
        $description->setUser($user);
        $form = $this->createForm(DescriptionType::class, $description);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($description);
            $entityManager->flush();

            return $this->redirectToRoute('course_show', array('id' => $course->getId()));
        }

        return $this->render('description/new.html.twig', [
            'description' => $description,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="description_show", methods={"GET"})
     */
    public function show(Description $description, EntityManagerInterface $entityManager): Response
    {
        $term = substr ( $description->getTermcall() ,  0,6 );
        $call = substr ( $description->getTermcall() ,  -6,6 );
        $course = $entityManager
            ->getRepository(Course::class)->findOneByTermcall($term, $call);
        return $this->render('description/show.html.twig', [
            'description' => $description,
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}/{courseid}/edit", name="description_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Description $description
     * @param $courseid
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Description $description, $courseid): Response
    {

        $form = $this->createForm(DescriptionType::class, $description);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('course_show', array('id' => $courseid));
        }

        return $this->render('description/edit.html.twig', [
            'description' => $description,
            'form' => $form->createView(),
            'courseid' => $courseid,
        ]);
    }

    /**
     * @Route("/{id}/{courseid}/delete", name="description_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Description $description, $courseid): Response
    {
        if ($this->isCsrfTokenValid('delete'.$description->getId(), $request->request->get('_token'))) {
            $entityManager->remove($description);
            $entityManager->flush();
        }

        return $this->redirectToRoute('course_show', array('id' => $courseid));
    }
}
