<?php

namespace App\Controller;

use Exception;
use App\Entity\News;
use App\Service\NewsService;
use App\Validator\NewsFilterValidator;
use App\Serializer\Normalizer\NewsNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/news", name="news")
 */
class NewsController extends AbstractController
{
    /**
     * Получить новости
     * @Route("/", name="list", methods={"GET"})
     * @param Request $request,
     * @param NewsService $newsService
     * @param NewsFilterValidator $newsFilterValidator
     * @return Response
     */
    public function list(
        Request $request,
        NewsService $newsService,
        NewsFilterValidator $newsFilterValidator
    ): Response
    {
        /**
         * @var array
         */
        $content = json_decode($request->getContent(), true) ?? [];
        try {
            $newsFilterValidator->validation($content);
        } catch(Exception $e) {
            return $this->json([
                "error" => unserialize($e->getMessage())
            ], $e->getCode());
        }

        /**
         * @var int
         */
        $pg = $content['pg'] ?? $this->getParameter('app.pg');
        /**
         * @var int
         */
        $on = $content['on'] ?? $this->getParameter('app.on');
        /**
         * @var string format: d-m-Y
         */
        $dateFilter = $content['dateFilter'] ?? null;
        /**
         * @var array
         */
        $tagIds = $content['tagIds'] ?? [];

        return $this->json([
            'list' => $newsService->get($pg, $on, $dateFilter, $tagIds)
        ]);
    }
    
    /**
     * Получить новость
     * @Route("/{news<\d+>}", name="item", methods={"GET"})
     * @param News $news
     * @return Response
     */
    public function item(
        ?News $news,
        NewsNormalizer $newsNormalizer
    ): Response
    {
        if (!$news) {
            return $this->json([
                'message' => 'Новость не найдена'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($newsNormalizer->normalize($news));
    }
}
