<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-07-29
 * Time: 22:27
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Job;
use AppBundle\Entity\JobNote;
use AppBundle\Form\JobSearchFormType;
use AppBundle\Form\JobSearchFormTypeData;
use AppBundle\Service\MarkdownTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobController extends Controller
{


    /**
     * @Route("/job/new")
     */
    public function newAction() {
        $job = new Job();
        $job->setSubFamily('project.example.com');
        $job->setStep1Id('S' . rand(1,100000));
        $job->setStep1Html('Html' . rand(1,100));
        $job->setStep1Statistics('Stats' . rand(1,100000));
        $job->setStep1Project('Project' . rand(1,100000));
        $job->setStep1Url('http://' . rand(1,100000) . '.com/');
        $job->setStep1DownloadedTime(new \DateTime('-1 month'));
        //$job->setIsPublished(1); // commented, because default is set on the property of the entity class

        $jobNote = new JobNote();
        $jobNote->setUsername('PMm');
        $jobNote->setUserAvatarFilename('ryan.jpeg');
        $jobNote->setNote('Just the string for testing.');
        $jobNote->setCreatedAt(new \DateTime('-1 month'));
        $jobNote->setJob($job);

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->persist($jobNote);
        $em->flush();

        return new Response('<html><body>Job created!</body></html>');
    }



    /**
     * @Route("/job/list/{offset}/{limit}")
     */
    public function listAction(Request $request, $offset= 0, $limit = 100) {

        $form = $this->createForm(JobSearchFormType::class);
        $form->handleRequest($request);

        $data = $form->getData();
        if(empty($data)) {
            $data = new JobSearchFormTypeData();
        }

        $em = $this->getDoctrine()->getManager();
        $jobs = $em->getRepository('AppBundle:Job')
            ->findAllPublishedOrderedByRecentlyActive($data, $offset, $limit);

        return $this->render('job/list.html.twig', [
            'jobs' => $jobs,
            'offset' => $offset,
            'jobSearchForm' => $form->createView(),
        ]);

    }

    /**
     * @Route("/job/{id}/notes", name="job_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Job $job)
    {
        //dump($job);

        // To remember: "git grep job_show_notes"
        // END. To remember

        $notes = [];
        foreach($job->getNotes() as $note) {
            $notes[] = [
                'id' => $note->getId(),
                'username' => $note->getUsername(),
                'avatarUri' => '/images/' . $note->getUserAvatarFilename(),
                'note' => $note->getNote(),
                'date' => $note->getCreatedAt()->format("Y-m-d")
            ];
        }

        $data = [
            'notes' => $notes
        ];

        return new JsonResponse($data);
    }


    /**
     * @Route("/job/{id}", name="job_show")
     */
    public function showAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('AppBundle:Job')
            ->findOneBy(['id' => $id]);

        if(!$job) {
            throw $this->createNotFoundException('No job found');
        }

        $transformer = $this->get('app.markdown_transformer');
        $jobDescription = $transformer->parse($job->getDescription());

//        $templating = $this->container->get('templating');
//        $html = $templating->render('job/show.html.twig', [
//            'jobId' => $jobId
//        ]);
//        return new Response($html);

//        $recentNotes = $job->getNotes()
//            ->filter(function(JobNote $note) {
//                return $note->getCreatedAt() > new \DateTime('-3 months');
//            });
        $recentNotes = $em->getRepository('AppBundle:JobNote')
            ->findAllRecentNotesForJob($job);

        return $this->render('job/show.html.twig', [
            'job' => $job,
            'recentNoteCount' => count($recentNotes),
            'jobDescription' => $jobDescription,
        ]);

    }




}