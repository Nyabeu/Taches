<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskFilter;
use App\Form\TaskType;
use App\Form\TaskFilterType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class TaskController extends AbstractController
{

    /**
     * @Route("/", name="task_index", methods={"GET"})
     */
    public function index(TaskRepository $taskRepository, Request $request): Response
    {
        $filter = new TaskFilter();
        $form = $this->createForm(TaskFilterType::class, $filter);
        $form->handleRequest($request);


        $tasks =  $taskRepository->findByCreateAndStatus($filter);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'form' => $form->createView(),

        ]);
    }

    /**
     * @Route("/new", name="task_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
            $this->addFlash('success', 'Tâche crée avec succès');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/edit/{id}", name="task_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setUpdateAt(new \Datetime('now'));
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Tâche modifié avec succès et date de mise à jour enregistrée');

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="task_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'Tâche supprimée avec succès');
        }

        return $this->redirectToRoute('task_index');
    }
}
