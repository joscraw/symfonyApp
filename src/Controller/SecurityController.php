<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Mailer\ResetPasswordMailer;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\UserRepository;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    use ServiceHelper;

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
     * @Route("/account-activation/{activationCode}", name="account_activation", requirements={"activationCode" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param $activationCode
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function accountActivationAction(Request $request, $activationCode)
    {
        /** @var User $user */
        $user = $this->userRepository->getByActivationCode($activationCode);
        if (!$user) {
            $this->addFlash('error', 'Invalid activation code!');
            return $this->redirectToRoute('welcome');
        }

        $user->setActivated(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('account_activated');
    }

    /**
     * @Route("/account-activated", name="account_activated", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function accountActivatedAction(Request $request): Response
    {
        return $this->render('security/account-activated.html.twig');
    }
}
