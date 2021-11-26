<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\OmdbApi;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/movie", name="movie_")
 */
#[Route('/movie', name: 'movie_')]
class MovieController extends AbstractController
{
    private OmdbApi $omdbApi;
    
    public function __construct(OmdbApi $omdbApi)
    {
        $this->omdbApi = $omdbApi;
        //$this->omdbApi = new OmdbApi($httpClient, '28c5b7b1', 'https://www.omdbapi.com');
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
     * @Route("/{imdbId}/import", name="import")
     */
    public function import($imdbId, EntityManagerInterface $entityManager): Response
    {
        $movieData = $this->omdbApi->requestOneById($imdbId);
        $movie = Movie::fromApi($movieData);
        $entityManager->persist($movie);
        $entityManager->flush();
        
        return $this->redirectToRoute('movie_latest');
    }
    
    /**
     * @Route("/{id<\d+>}", name="show")
     */
    public function show(int $id, Movie $movie)
    {
        dump($movie);
        $this->denyAccessUnlessGranted('MOVIE_SHOW', $movie);
        
        return $this->render('movie/show.html.twig', [
            'id' => $id,
        ]);
    }
    
    /**
     * @Route("/latest", name= "latest")
     */
    public function latest(MovieRepository $movieRepository)
    {
        $movies = $movieRepository->findBy([], ['id' => 'DESC']);
        
        dump($movies);
        
        return $this->render('movie/latest.html.twig', [
            'movies' => $movies
        ]);
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
