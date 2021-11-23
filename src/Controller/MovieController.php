<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\OmdbApi;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/movie", name="movie_")
 */
#[Route('/movie', name: 'movie_')]
class MovieController extends AbstractController
{
    private OmdbApi $omdbApi;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->omdbApi = new OmdbApi($httpClient, '28c5b7b1', 'https://www.omdbapi.com');
    }
    /**
     * @Route("/", name="movie")
     */
    public function index(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }
    
    /**
     * @Route("/{id<\d+>}", name="show")
     */
    public function show(int $id)
    {
        return $this->render('movie/show.html.twig', [
            'id' => $id,
        ]);
    }
    
    /**
     * @Route("/latest", name= "latest")
     */
    public function latest()
    {
        return $this->render('movie/latest.html.twig');
    }
    
    /**
     * @Route("/search", name= "search")
     */
    public function search(Request $request)
    {
        $keyword = $request->query->get('keyword', 'Blue');
        $movies = $this->omdbApi->requestAllBySearch($keyword);
        dump($movies);
        
        return $this->render('movie/search.html.twig', [
            'movies' => $movies,
            'keyword' => $keyword,
        ]);
    }
}
