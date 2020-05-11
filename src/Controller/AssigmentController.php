<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Assigment;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\OnTask;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Skill;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Services\levelsCacheMenager;

class AssigmentController extends AbstractController
{
    /**
     * @Route("/assigment", name="assigment_list")
     */
    public function index()
    {
        $assigments = $this->getDoctrine()
            ->getRepository(Assigment::class)
            ->findAll();

        return $this->render("assigment/index.html.twig", [
            'assigments' => $assigments
            ]);
    }
    /**
     * @Route("/assigment/create/{number}", name="assigment_create")
     */
    public function create($number, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Skill::class);
        $qb = $repository->createQueryBuilder('skill');
        $qb->select('count(skill.id)');

        $count = $qb->getQuery()->getSingleScalarResult();

        if($number>$count || $number<0)
        {
            $this->addFlash("failed", "Number must be grater than zero and less than {$count}");
            return $this->redirectToRoute('assigment_create_init');
        }
        $form = $this->createFormBuilder()
            ->add('title')
            ->add('description');

        for($i=0; $i<$number; $i++)
        {
            $form->add("skill{$i}", EntityType::class, [
                'label' => 'Skill',
                'class' => Skill::class,
                'choice_label' => function ($skill) {
                    return $skill -> getName();
                }])
            ->add("value{$i}", IntegerType::class, [
                'label' => 'Exp value',
                'attr' => [
                    'class' => 'w-75'
                ]]);
        }

        $form->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary float-right'
            ]
        ]);

        $form = $form->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $assigment = new Assigment();
            $onTask = array($number);

            for($i=0; $i<$number; $i++)
            {
                $onTask[$i] = new OnTask;
            }

            $assigment->setTitle($data['title']);
            $assigment->setDescription($data['description']);
            for($i=0; $i<$number; $i++)
            {
                $assigment->addForTask($onTask[$i]);
            }

            for($i=0; $i<$number; $i++)
            {
                $onTask[$i]->setAssigment($assigment);
                $onTask[$i]->setSkill($data["skill{$i}"]);
                $onTask[$i]->setValue($data["value{$i}"]);
                $em -> persist($onTask[$i]);
            }

            $em -> persist($assigment);
            $em -> flush();

            $this->addFlash('assig_created', 'Assigment was created');
            return $this->redirectToRoute('assigment_list');
        }

        return $this->render('assigment/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/assigment/create", name="assigment_create_init")
     */
    public function createInit(Request $request)
    {
        $form = $this->createFormBuilder()
        ->add('number', IntegerType::class, [
            'label' => "Related skills number",
        ])
        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary float-right'
            ]
        ])
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $data = $form->getData();
            return $this->redirectToRoute('assigment_create', ['number' => $data['number']]);
        }

        return $this->render('assigment/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/assigment/delete/{id}", name="assigment_delete", methods={"DELETE"})
     */

    public function assigmentDelete($id)
    {
        $assigment = $this->getDoctrine()
            ->getRepository(Assigment::class)
            ->findOneBy(['id'=>$id]);

        $em = $this->getDoctrine()->getManager();

        $onTasks = $this->getDoctrine()
            ->getRepository(OnTask::class)
            ->findBy(['assigment'=>$id]);

        foreach($onTasks as $onTask)
        {
            $em->remove($onTask);
        }

        $em->remove($assigment);
        $em->flush();

        $this->addFlash('assig_deleted', "Assigment was deleted");
        return $this->redirectToRoute('assigment_list');
    }

    /**
     * @Route("/assigment/complete/{id}", name="assigment_complete")
     */

    public function assigmentComplete($id, levelsCacheMenager $lvlMenager)
    {
        $em = $this->getDoctrine()->getManager();
        $assigment = $em->getRepository(Assigment::class)
            ->findOneBy(['id'=>$id]);

        foreach($assigment->getForTask() as $onTask)
        {
            $skill = $onTask->getSkill();
            $level = $skill->getLevel();
            $exp = $level->getExpirience()+$onTask->getValue();

            if($lvlMenager->getLevel($skill->getName()) > $onTask->getValue())
            {
                $level->setExpirience($exp);
            }else
            {
                $next = $this->calcNextLevel($level->getLevel(), 0, $exp);
                $nextLevel = $next[0];
                $nextExp = $next[1];

                $level->setLevel($nextLevel);
                $level->setExpirience($nextExp);
            }
            $em->flush();
            return $this->redirectToRoute('assigment_list');
        }
    }

    function calcNextLevel(int $level, float $exp, float $val)
    {
        $nextExp = round(20*pow(1.2 , $level-1), 2);
        if($nextExp+$exp >= $val) return [$level, $val-$exp];
        else return $this->calcNextLevel($level+1, $exp+$nextExp, $val);
    }
}
