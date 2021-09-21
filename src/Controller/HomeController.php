<?php

namespace App\Controller;

use App\Service\DatabaseFinder\DatabaseFinderInterface;
use App\Service\DatabaseManager\DatabaseManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;

class HomeController extends AbstractController
{
    /**
     * @var DatabaseManagerInterface
     */
    private $databaseManager;

    public function __construct(DatabaseManagerInterface $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(KernelInterface $kernel,string $databasesDir, Request $request): Response
    {
        $form = $this->createFormBuilder();

        $databases = $this->databaseManager->getList();

        foreach ($databases as $database){
            $form->add($database->getFilenameWithoutExtension(),CheckboxType::class,[
                'required' => false
            ]);
        }

        $form = $form
            ->add('submit',SubmitType::class,[
                'label' => 'Load news'
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDatabases = $form->getData();
            foreach($selectedDatabases as $name => $db){

                if(!$db){
                    continue;
                }

                $application = new Application($kernel);
                $application->setAutoExit(false);

                $file = $databasesDir.$name.'.sql';

                $input = new ArrayInput([
                    'command' => 'doctrine:database:import',
                    'file' => $file
                ]);

                $output = new BufferedOutput();
                $application->run($input, $output);

                $content = $output->fetch();
            }

            return $this->redirectToRoute('news');

        }


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'files' => $databases,
            'form' => $form->createView()
        ]);
    }
}
