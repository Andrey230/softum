<?php

namespace App\Controller;

use App\Service\NewsManager\NewsManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NewsController extends AbstractController
{
    /**
     * @var NewsManagerInterface
     */
    private $newsManager;

    public function __construct(
        NewsManagerInterface $newsManager
    )
    {

        $this->newsManager = $newsManager;
    }

    /**
     * @Route("/news", name="news")
     */
    public function index(Request $request): Response
    {
        $news = $this->newsManager->findNews();

        $form = $this->createFormBuilder();

        foreach ($news as $table => $rows){
            $form->add($table,CheckboxType::class,[
                'label' => "Number of news from $table - ".count($rows),
                'required' => false
            ]);
        }

        $form = $form->add('fileType',ChoiceType::class,[
                'choices' => [
                    'xml' => 'xml',
                    'csv' => 'csv',
                    'json' => 'json'
                ]
            ])
            ->add('submit',SubmitType::class,[
                'label' => 'Download'
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $fileType = $form->get('fileType')->getData();

            $fileData = [];

            foreach ($data as $table => $isChecked){
                if(array_key_exists($table,$news)){
                    if($isChecked){
                        $fileData[$table] = $news[$table];
                    }
                }
            }

            $encoder = $this->getEncoder($fileType);
            $fileData = $encoder->encode($fileData,$fileType);

            $response = new Response();
            $response->headers->add([
                'Content-Type' => $this->getMimeType($fileType)
            ]);

            $response->setContent($fileData);

            return $response;

        }

        return $this->render('news/index.html.twig', [
            'news' => $news,
            'form' => $form->createView()
        ]);
    }

    private function getEncoder($fileType):EncoderInterface
    {
        switch ($fileType){
            case 'xml':
                return new XmlEncoder();
            case 'csv':
                return new CsvEncoder();
            case 'json':
                return new JsonEncoder();
        }

        throw new \Exception("Undefined encoder for $fileType");
    }

    private function getMimeType(string $fileType)
    {
        switch ($fileType){
            case 'xml':
                return 'application/xhtml+xml';
            case 'csv':
                return 'text/csv';
            case 'json':
                return 'application/json';
            default:
                return 'text/html';
        }
    }
}
