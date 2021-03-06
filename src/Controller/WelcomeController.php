<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class WelcomeController
 * @package App\Controller
 */
class WelcomeController extends AbstractController
{

    /**
     * @Route("/", name="welcome")
     * @param Request $request
     * @return Response
     */
    public function welcome(Request $request): Response
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/image-upload", name="welcome_index", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function indexAction(Request $request) {
        $user = $this->getUser();
        return $this->render('welcome/index.html.twig', [
            'user' => $user
        ]);
    }
}