<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailCheckType;
use App\Form\ForgotPasswordType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordType;
use App\Http\ApiResponse;
use App\Mailer\ResetPasswordMailer;
use App\Model\EmailCheck;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\AcceptHeader;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/", name="welcome")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response
     */
    public function welcome(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'action' => $this->generateUrl('welcome'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 'error' => $error,
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/email-check", name="email_check", methods={"GET", "POST"}, options = {"expose" = true })
     * @param Request $request
     * @return Response
     */
    public function emailCheckAction(Request $request): Response
    {
        $emailCheck = new EmailCheck();
        $form = $this->createForm(EmailCheckType::class, $emailCheck, []);

        $acceptHeader = AcceptHeader::fromString($request->headers->get('Accept'));

        if ($acceptHeader->has('application/json')) {
            $clearMissing = $request->getMethod() != 'PATCH';
            $form->submit($request->request->all(), $clearMissing);

            if (!$form->isValid()) {
                $errors = $this->getErrorsFromForm($form);
                return new ApiResponse("Error submitting form.", null, $errors, 400);
            } else {

                /** @var EmailCheck $emailCheck */
                $emailCheck = $form->getData();

                $user = $this->userRepository->findOneBy([
                    'email' => $emailCheck->getEmailAddress()
                ]);

                if(!$user) {
                    return new ApiResponse("User does not exist in the system", [
                        'email_exists' => false,
                        'verified' => false
                    ]);
                }

                if($user && !$user->getIsVerified()) {
                    return new ApiResponse("User exists but is not verified in the system", [
                        'email_exists' => true,
                        'verified' => false
                    ]);
                }
            }

            return new ApiResponse("User exists and has been verified", [
                'email_exists' => true,
                'verified' => true
            ]);


        } else {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                /** @var EmailCheck $emailCheck */
                $emailCheck = $form->getData();

                $user = $this->userRepository->findOneBy([
                    'email' => $emailCheck->getEmailAddress()
                ]);

                if(!$user) {
                    $this->addFlash('error', 'That user does not exist in the system.');
                    return $this->redirectToRoute('email_check');
                }

                if($user && !$user->getIsVerified()) {
                    $this->addFlash('error', 'That user is not verified yet in the system.');
                    return $this->redirectToRoute('email_check');
                }

                return new Response("successful");
            }

            return $this->render('security/email_check.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/forgot-password", name="forgot_password_form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function forgotPasswordFormAction(Request $request): Response
    {
        $forgotPassword = new ForgotPassword();

        $form = $this->createForm(ForgotPasswordType::class, $forgotPassword, [
            'action' => $this->generateUrl('forgot_password_form'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if(!$form->isValid()) {

                return $this->render('security/forgot_password.html.twig', [
                    'form' => $form->createView()
                ]);


            } else {

                /** @var ForgotPassword $forgotPassword */
                $forgotPassword = $form->getData();
                $emailAddress = $forgotPassword->getEmailAddress();

                /** @var User $user */
                $user = $this->userRepository->getByEmailAddress($emailAddress);

                // If the forgot-email function was used within the last 24 hours for
                // this user, render the form with an appropriate validation message.
                $currentTokenTimestamp = $user->getPasswordResetTokenTimestamp();
                if ($currentTokenTimestamp && $currentTokenTimestamp >= new \DateTime('-23 hours 59 minutes 59 seconds')) {
                    $errorMessage = 'Sorry, an email containing password reset instructions has been sent to this email address within the last 24 hours';
                    $form->addError(new FormError($errorMessage));

                    return $this->render('security/forgot_password.html.twig', [
                        'form' => $form->createView()
                    ]);
                }

                $user->setPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->resetPasswordMailer->send($user);

                return $this->render('security/password-reset-code-sent.html.twig', [
                    'user' => $user
                ]);
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/password-created", name="password_created", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function passwordCreatedAction(Request $request): Response
    {
        return $this->render('security/password-created.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="reset_password", requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param string $token
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function resetPasswordAction(Request $request, $token)
    {

        $user = $this->userRepository->getByPasswordResetToken($token);

        if(!$user) {
            return $this->render('security/reset-password-error.html.twig');
        }

        $resetPassword = new ResetPassword();

        $form = $this->createForm(ResetPasswordType::class, $resetPassword, [
            'action' => $this->generateUrl('reset_password', ['token' => $token]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            if (!$form->isValid()) {

                return $this->render('security/reset_password_form.html.twig', [
                    'form' => $form->createView()
                ]);

            } else {

                /** @var ResetPassword $resetPassword */
                $resetPassword = $form->getData();

                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $resetPassword->getPassword()
                ));

                $user->clearPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('password_created');
            }
        }

        return $this->render('security/reset_password_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param RedirectResponse $redirectResponse
     * @return RedirectResponse|Response
     */
    protected function generatePasswordActionResponse(Request $request, User $user, RedirectResponse $redirectResponse)
    {
        $form = $this->createForm('app_set_password');

        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST) && $form->isValid()) {
            /** @var ChangePassword $changePassword */
            $changePassword = $form->getData();

            $encodedPassword = $this->passwordEncoder->encodePassword($user, $changePassword->getPassword());
            $user->setPassword($encodedPassword)
                ->clearPasswordResetToken()
                ->setIsPasswordSetUp(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush($user);

            return $redirectResponse;
        }

        $returnUrl = $request->headers->get('referer');

        if (!empty($returnUrl)) {
            $session = $this->get('session');
            $session->set('return_destination', $returnUrl);
        }

        return $this->render('security/set-password.html.twig', array(
            'method' => 'post',
            'form'   => $form->createView(),
            'user'   => $user,
        ));
    }
}
