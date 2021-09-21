<?php

namespace App\Controller;

use App\Service\DatabaseManager\DatabaseManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DatabaseController extends AbstractController
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
     * @Route("/databases", name="databases")
     */
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $databases = $this->databaseManager->getList();


        $form = $this->createFormBuilder()
            ->add('file', FileType::class,[
                'label' => 'Upload database (*.sql)',
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            //'application/sql'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid .sql file',
                        'maxSize' => '10000k'
                    ])
                ],
            ])
            ->add('submit',SubmitType::class,[
                'label' => 'Add database'
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $databaseFile */
            $databaseFile = $form->get('file')->getData();

            if ($databaseFile) {
                $fileName = $databaseFile->getClientOriginalName();
                $this->databaseManager->create($databaseFile,$fileName);
            }
        }

        return $this->render('database/index.html.twig', [
            'databases' => $databases,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/databases/remove", name="databases_remove", methods={"DELETE"})
     */
    public function removeDatabase(Request $request)
    {

        $requestContent = json_decode($request->getContent(),true);

        $name = $requestContent['name'] ?? null;

        if(!is_null($name)){
            $this->databaseManager->remove($name);
        }

        return $this->json([],Response::HTTP_NO_CONTENT);
    }
}
