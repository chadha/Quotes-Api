<?php

namespace App\Controller\Api;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class QuoteController extends AbstractController
{
    /**
     * @var QuoteRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * PropertyController constructor.
     * @param QuoteRepository $repository
     * @param ObjectManager $em
     */
    public function __construct(QuoteRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/quote/{id}", methods={"GET"}, name="quote_show")
     *
     * @param Quote $quote
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function showAction(Quote $quote, SerializerInterface $serializer): Response
    {
        $data = $serializer->serialize($quote, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /**
     * @Route("/quotes", name="quote_create", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $data = $request->getContent();
        $article = $this->get('serializer')->deserialize($data, 'AppBundle\Entity\Quote', 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return new Response('', Response::HTTP_CREATED);
    }

}