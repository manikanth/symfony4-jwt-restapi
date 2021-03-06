<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use App\Entity\Meeting;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Meeting Controller
 *
 * @Route("/api/v1")
 */

class MeetingController extends Controller {

	/**
     * Create Meeting.
     * @FOSRest\Post("/meeting")
     *
     * @return array
     */
    public function postMeetingAction(Request $request)
    {
        $postdata = json_decode($request->getContent());
        $meeting = new Meeting();
        $meeting->setName($postdata->name);
        $meeting->setDescription($postdata->description);
        $meeting->setDateTime(new \DateTime($postdata->date));
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($postdata->userid);
        $meeting->setUser($user);
        $em->persist($meeting);
        $em->flush();
        $response = array(
            'id' => $meeting->getId(),
            'name' => $meeting->getName(),
            'description' => $meeting->getDescription(),
            'date' => $meeting->getDateTime()
        );
        return View::create($response, Response::HTTP_CREATED , []);
    }

    /**
     * Lists all Meetings.
     * @FOSRest\Get("/meeting")
     *
     * @return array
	 */
	public function getMeetingAction()
	{
		$repository = $this->getDoctrine()->getRepository(Meeting::class);

		// query for a single Product by its primary key (usually "id")
        $meetings = $repository->findall();
        // Move this in Meeting normalizer
        $response = array();
        foreach($meetings as $meeting) {
            // find users
            $users = [];
            foreach($meeting->getUsers() as $user) {
                $users[] = array(
                    'id' => $user->getId(),
                    'fullname' => $user->getFullName(),
                    'email' => $user->getEmail()
                );
            }

            $response[] = array(
                'id' => $meeting->getId(),
                'name' => $meeting->getName(),
                'description' => $meeting->getDescription(),
                'date' => $meeting->getDateTime(),
                'users' => $users
            );
        }

		return View::create($response, Response::HTTP_OK , []);
    }

    /**
     * Update an Meeting.
     * @FOSRest\Put(path = "/meeting/{id}")
     *
     * @return array
	 */
    public function putMeetingAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $meeting = $em->getRepository(Meeting::class)->find($id);
        if(!$meeting) {
            throw new HttpException(404, 'Meeting not found');
        }
        $postdata = json_decode($request->getContent());
        $meeting->setName($postdata->name);
        $meeting->setDescription($postdata->description);
        $meeting->setDateTime(new \DateTime($postdata->date));
        $em->persist($meeting);
        $em->flush();
		return View::create($meeting, Response::HTTP_OK , []);
    }

    /**
     * Delete an Meeting.
     *
     * @FOSRest\Delete(path = "/meeting")
     *
     * @return array
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $meeting = $em->getRepository(Meeting::class)->find($request->get('meeting_id'));
        if(!$meeting) {
            throw new HttpException(404, 'Meeting not found');
        }
        $em->remove($meeting);
        $em->flush();
        return View::create(null, Response::HTTP_NO_CONTENT);
    }

}
