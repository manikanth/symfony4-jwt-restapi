<?php

namespace App\Controller;

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

class MeetingRegistrationController extends Controller
{
	/**
     * Register
     * @FOSRest\Post("/meeting/registration")
     *
     * @return array
     */
    public function registerUserMeetingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        // Check user already exists
        $user = $userManager->findUserBy(array('id' => $request->get('user_id')));
        if(!$user) {
            throw new HttpException(400, 'Userid invalid.');
        }

        $meeting = $em->getRepository(Meeting::class)->find(array('id' => $request->get('meeting_id')));
        if(!$meeting) {
            throw new HttpException(400, 'Meetingid invalid.');
        }

        $user->setMeeting($meeting);
        $meeting->setUser($user);

        $em->persist($user);
        $em->persist($meeting);
        $em->flush();
        return View::create('', Response::HTTP_NO_CONTENT , []);
    }

    /**
     * Register
     * @FOSRest\DELETE("/meeting/registration")
     *
     * @return array
     */
    public function unregisterUserMeetingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        // Check user already exists
        $user = $userManager->findUserBy(array('id' => $request->get('user_id')));
        if(!$user) {
            throw new HttpException(400, 'Userid invalid.');
        }

        $meeting = $em->getRepository(Meeting::class)->find(array('id' => $request->get('meeting_id')));
        if(!$meeting) {
            throw new HttpException(400, 'Meetingid invalid.');
        }

        $user->removeMeeting($meeting);
        $meeting->removeUser($user);
        $em->remove($meeting);
        $em->remove($user);
        $em->flush();
        return View::create('', Response::HTTP_NO_CONTENT , []);
    }
}
