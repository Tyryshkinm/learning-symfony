<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Form\MemberType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberController extends Controller
{
    public function indexAction()
    {
        $members = $this->getDoctrine()
            ->getRepository('AppBundle:Member')
            ->findAll();
        return $this->render('member/index.html.twig', ['members' => $members]);
    }

    public function createAction(Request $request, $memberId = null)
    {
        $member = null;

        if ($memberId != null) {
            $member = $this->getDoctrine()
                ->getRepository(Member::class)
                ->find($memberId);

            if (!$member) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();
            return $this->redirectToRoute('member_index');
        }
        return $this->render('member/create.html.twig', ['form' => $form->createView()]);
    }
}
