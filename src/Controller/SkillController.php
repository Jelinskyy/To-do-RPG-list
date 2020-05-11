<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SkillType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Skill;
use App\Repository\SkillRepository;
use App\Entity\Assigment;
use App\Entity\OnTask;
use App\Services\levelsCacheMenager;

class SkillController extends AbstractController
{
    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($skill);
            $em->flush();

            $this->addFlash('skill_created', "Skill was deleted");
            return $this->redirectToRoute('list');
        }

        return $this->render('skill/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/skill/delete/{id}", name="skill_delete", methods={"DELETE"})
     */

    public function delete($id)
    {
        $skill = $this->getDoctrine()
            ->getRepository(Skill::class)
            ->findOneBy(['id'=>$id]);

        $em = $this->getDoctrine()->getManager();

        $onTasks = $this->getDoctrine()
            ->getRepository(OnTask::class)
            ->findBy(['skill'=>$id]);

        foreach($onTasks as $onTask)
        {
            $assigment = $this->getDoctrine()
                ->getRepository(Assigment::class)
                ->findOneBy(['id'=>$onTask->getAssigment()->getId()]);

            if(count($assigment->getForTask()) <= 1) $em->remove($assigment);
            else $assigment->removeForTask($onTask);

            $em->remove($onTask);
        }

        $em->remove($skill);
        $em->flush();

        $this->addFlash('skill_deleted', "Skill was deleted");
        return $this->redirectToRoute('list');
    }

    /**
     * @Route("/", name="list")
     */
    public function index(SkillRepository $skillRepository, levelsCacheMenager $lvlMenager)
    {
        $skills = $skillRepository->findAll();
        $levels = array();

        foreach($skills as $skill)
        {
            $val = $skill->getLevel()->getLevel();
            if(empty($levels[$val]))
            {
                $levels[$val] = $lvlMenager->getLevel($skill->getName());
            }
        }

        return $this->render('skill/index.html.twig', ['skills' => $skills, 'levels' => $levels]);
    }
}