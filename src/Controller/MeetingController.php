<?php

namespace App\Controller;

use Exception;
use App\Entity\Meeting;
use App\Entity\Customer;
use App\Form\MeetingType;
use App\Repository\MeetingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/meeting', name: 'app_meeting.')]
class MeetingController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(MeetingRepository $meetingRepository): Response
    {
        return $this->render('meeting/index.html.twig', [
            'meetings' => $meetingRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Customer $customer, Filesystem $fs): Response
    {
        $meeting = new Meeting();
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $filename = rand(100000, 999999);

                //Evenèment au format ICS
                $ics = "BEGIN:VCALENDAR\n";
                $ics .= "VERSION:2.0\n";
                $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
                $ics .= "BEGIN:VEVENT\n";
                $ics .= "X-WR-TIMEZONE:Europe/Paris\n";
                $ics .= "DTSTART:" . $data->getStartDate()->format('Ymd\THis\Z') . "\n";
                $ics .= "DTEND:" . $data->getEndDate()->format('Ymd\THis\Z') . "\n";
                $ics .= "SUMMARY:" . $data->getSubject() . "\n";
                $ics .= "LOCATION:" . $data->getAddress() . "\n";
                $ics .= "DESCRIPTION:" . $data->getDetails() . "\n";
                $ics .= "END:VEVENT\n";
                $ics .= "END:VCALENDAR\n";

                $path = $this->getParameter('kernel.project_dir') . '/public/';
                if (!$fs->exists($path . 'files')) {
                    $fs->mkdir($path . 'files');
                }

                if (!$fs->exists($path . 'files/' . $filename . '.ics')) {
                    $fs->touch('files/' . $filename . '.ics');
                    $fs->dumpFile('files/' . $filename . '.ics', $ics);
                } else {
                    $fs->dumpFile('files/' . $filename . '.ics', $ics);
                }

                $meeting->setCustomer($customer);
                $meeting->setFileName($filename);
                $entityManager->persist($meeting);
                $entityManager->flush();
                $this->addFlash('success', 'Meeting has been successfully created.');
                return $this->redirectToRoute('app_customer.show', ['id' => $customer->getId()], Response::HTTP_SEE_OTHER);
            } catch (IOExceptionInterface $e) {
                $this->addFlash('danger', "An error occurred while creating your directory at " . $e->getPath());
            }
        }

        return $this->render('meeting/new.html.twig', [
            'customer' => $customer,
            'meeting' => $meeting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Meeting $meeting): Response
    {
        return $this->render('meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Meeting $meeting, EntityManagerInterface $entityManager): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/files/';
        $file = $meeting->getFileName();
        if ($this->isCsrfTokenValid('delete' . $meeting->getId(), $request->getPayload()->get('_token'))) {
            try {
                unlink($path . $file . '.ics');
                $entityManager->remove($meeting);
                $entityManager->flush();
                $this->addFlash('success', 'meeting deleted');
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_customer.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/save/{id}', name: "download")]
    public function download(Meeting $meeting): BinaryFileResponse
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/files/';
        // load the file from the filesystem
        $file = new File($path . $meeting->getFileName() . '.ics');

        return $this->file($file);
    }
}
