<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/")
 */
class EventController extends Controller
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $dql   = "SELECT e FROM App:Event e";
        $query = $entityManager->createQuery($dql);

        $events = $this->get('knp_paginator')->paginate(
        // Doctrine Query, not results
            $query,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            3
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setCreatedAt(new \DateTime('now'));

            $user = $this->getUser();
            $event->setUser($user);

            $image = $request->files->get('event')['image'];
            $uploadDirectory = $this->getParameter('upload_directory');


            if ($image) {

                $filename = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $uploadDirectory,
                    $filename
                );
                $event->setImage($filename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/event/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/like/{id}", name="event_like", methods={"GET"})
     */
    public function like(Security $security, Event $event) : Response
    {
        $user = $security->getUser();

        if (!$event->getUserLike()->contains($user)) {
            $event->addUserLike($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
        } else {
            $event->removeUserLike($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
        }
        return $this->redirect('/..');
    }

    /**
     * @Route("/edit/{id}", name="event_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Event $event): Response
    {

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $request->files->get('event')['image'];
            $uploadDirectory = $this->getParameter('upload_directory');

            $event = $form->getData();

            if ($image) {

                $filename = md5(uniqid()) . '.' . $image->guessExtension();

                $image->move(
                    $uploadDirectory,
                    $filename
                );

                $event->setImage($filename);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }
        return $this->redirectToRoute('event_index');
    }



}
