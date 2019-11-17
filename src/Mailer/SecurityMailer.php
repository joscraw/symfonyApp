<?php

namespace App\Mailer;

use App\Entity\User;

/**
 * Class SecurityMailer
 * @package App\Mailer
 */
class SecurityMailer extends AbstractMailer
{
    public function sendPasswordReset(User $user) {

        $resetPasswordUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'reset_password',
                array('token' => $user->getPasswordResetToken())
            );

        $message = (new \Swift_Message('Password Reset'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordResetEmail.html.twig',
                    ['user' => $user, 'resetPasswordUrl' => $resetPasswordUrl]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    public function sendAccountActivation(User $user) {

        $accountActivationUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'account_activation',
                array('activationCode' => $user->getActivationCode())
            );

        $message = (new \Swift_Message('Activate Account'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/accountActivationEmail.html.twig',
                    ['user' => $user, 'accountActivationUrl' => $accountActivationUrl]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    /**
     * @param User $user
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendInvitation(User $user) {

        $passwordSetUrl = $this->getFullyQualifiedBaseUrl().$this->router->generate(
                'set_password',
                array('token' => $user->getInvitationCode())
            );

        $message = (new \Swift_Message('Password Setup'))
            ->setFrom($this->siteFromEmail)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/passwordSetupEmail.html.twig',
                    ['user' => $user, 'passwordSetUrl' => $passwordSetUrl]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}