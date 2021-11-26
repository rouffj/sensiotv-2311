<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use App\OmdbApi;

/**
 * bin/console le-juste-titre "Sky"
 * 
 *  Ceci est le synopsis d'un film contentant Sky
 *. 
 *. > Quel est le nom de ce film ?
 *. Skyfall
 *
 * Le film est sortie en 2010
 */
class LeJusteTitreCommand extends Command
{
    protected static $defaultName = 'app:le-juste-titre';
    protected static $defaultDescription = 'Add a short description for your command';

    private $omdbApi;

    public function __construct(OmdbApi $omdbApi)
    {
        $this->omdbApi = $omdbApi;
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('keyword', InputArgument::REQUIRED, 'Le mot clé du film à deviner.')
        ;
    }

    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
 
        $keyword = $input->getArgument('keyword');
        $movies = $this->omdbApi->requestAllBySearch($keyword);
        //dump($movies);
        
        // RANDOMIZE
        $randomIndex = rand(0, count($movies['Search'])-1 );
        $randomImdbId = $movies['Search'][$randomIndex]['imdbID'];
        $selectedMovie = $this->omdbApi->requestOneById($randomImdbId, ['plot' => 'full']);
        
        $output->writeln('# Synopsis du film #');
        $output->writeln($selectedMovie['Plot']);
        //dump($selectedMovie);
        
        // Choices
        $choices = [];
        foreach ($movies['Search'] as $movie) {
            $choices[] = $movie['Title'];
        }
        
        $question = new ChoiceQuestion('A quel film correspond ce synopsis ?', $choices);
        $question->setMaxAttempts(2);
        $question->setValidator(function($answerIndex) use($selectedMovie, $choices) {
            #dump($answerIndex, $choices[$answerIndex], $selectedMovie['Title']);
            if ($choices[$answerIndex] !== $selectedMovie['Title']) {
                throw new \invalidArgumentException('Veuillez essayer un nouveau film');
            }
            
            return $answerIndex;
        });

        try {
            $playerMovieTitle = $io->askQuestion($question, $choices);
            $io->success('👍 Bravo! vous avez trouvez le bon film 👍');
        } catch (\InvalidArgumentException $e) {
            $io->error('💩 La bonne réponse était "'.$selectedMovie['Title'].'" 💩');
        }

        return Command::SUCCESS;
    }
}
