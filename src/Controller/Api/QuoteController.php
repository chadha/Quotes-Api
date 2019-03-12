<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Quote;
use App\Repository\QuoteRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;


class QuoteController extends AbstractFOSRestController
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
     * @Rest\Post(
     *    path = "/quotes",
     *    name = "app_quote_create"
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "quote",
     *     converter="fos_rest.request_body",
     *     options={
     *         "validator"={ "groups"="Create" }
     *     }
     * )
     */
    public function createAction(Quote $quote, ConstraintViolationList $violations)
    {

        if (count($violations)) {
            return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $authorRepository = $this->getDoctrine()->getRepository(Author::class);
        if($authorRepository->findOneBy(['id' => $quote->getAuthor()->getId()]))
        {
            $author =$authorRepository->findOneBy(['id' => $quote->getAuthor()->getId()]);
        }
        else if( $quote->getAuthor()->getFullName())
        {
            //@todo create ExceptionListener ExceptionHandler

            $author = new Author();
            $author->setFullName($quote->getAuthor()->getFullName());
            $emAuthor = $this->getDoctrine()->getManager();
            $emAuthor->persist($author);
            $emAuthor->flush();
        }

        $quote->setAuthor($author);
        $em->persist($quote);
        $em->flush();


        return $this->view(
            $quote, Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('quote_show',
                ['id' => $quote->getId(), UrlGeneratorInterface::ABSOLUTE_URL])
            ]);
    }

}