<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Phone;
use AppBundle\Form\MemberType;
use AppBundle\Form\Object\SearchMemberObject;
use AppBundle\Form\SearchMemberType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchObject = new SearchMemberObject();

        $form = $this->createForm(SearchMemberType::class, $searchObject);
        $form->handleRequest($request);

        $members = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->getMembersForSearch($searchObject->getFirstName(), $searchObject->getLastName(), $searchObject->getEmail());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $members,
            $request->query->getInt('page', 1),
            3
        );

//        return $this->render('member/index.html.twig', ['members' => $members, 'form' => $form->createView()]);
        return $this->render('member/index.html.twig', ['pagination' => $pagination, 'form' => $form->createView()]);
    }

    public function createAction(Request $request, $memberId = null)
    {
        $member = null;

        $em = $this->getDoctrine()->getManager();

        if ($memberId != null) {
            $member = $em
                ->getRepository(Member::class)
                ->find($memberId);

            if (!$member) {
                throw new NotFoundHttpException();
            }

            $originalPhones = new ArrayCollection();

            foreach ($member->getPhones() as $phone) {
                $originalPhones->add($phone);
            }
        }

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();

            if (isset($originalPhones)){
                foreach ($originalPhones as $phone) {
                    if (false === $member->getPhones()->contains($phone)) {

                        $member->removePhone($phone);
                        $em->remove($phone);
                    }
                }
            }

            $em->persist($member);
            $em->flush();

            return $this->redirectToRoute('member_index');
        }
        return $this->render('member/create.html.twig', ['form' => $form->createView()]);
    }

    public function destroyAction($memberId = null)
    {
        if ($memberId != null) {
            $em = $this->getDoctrine()->getManager();

            $member = $em
                ->getRepository(Member::class)
                ->find($memberId);

            $em->remove($member);
            $em->flush();
            if (!$member) {
                throw new NotFoundHttpException();
            }
            return $this->redirectToRoute('member_index');
        }
        return $this->redirectToRoute('hello_index');
    }
}
