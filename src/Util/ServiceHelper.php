<?php

namespace App\Util;

use App\Mailer\ResetPasswordMailer;
use App\Mailer\SecurityMailer;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

trait ServiceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Packages
     */
    private $assetsManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     * @var GuardAuthenticatorHandler $guardHandler,
     */
    private $guardHandler;

    /**
     * @var LoginFormAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TokenStorageInterface
     */
    private $securityToken;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * @var ApiTokenRepository
     */
    private $apiTokenRepo;

    /**
     * @var SecurityMailer
     */
    private $securityMailer;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var ImageCacheGenerator
     */
    private $imageCacheGenerator;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * ServiceHelper constructor.
     * @param EntityManagerInterface $entityManager
     * @param Packages $assetsManager
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     * @param ValidatorInterface $validator
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param Environment $twig
     * @param TokenStorageInterface $securityToken
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ResetPasswordMailer $resetPasswordMailer
     * @param ApiTokenRepository $apiTokenRepo
     * @param SecurityMailer $securityMailer
     * @param UploaderHelper $uploaderHelper
     * @param ImageCacheGenerator $imageCacheGenerator
     * @param CacheManager $cacheManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Packages $assetsManager,
        UserRepository $userRepository,
        RouterInterface $router,
        ValidatorInterface $validator,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        Environment $twig,
        TokenStorageInterface $securityToken,
        SerializerInterface $serializer,
        UserPasswordEncoderInterface $passwordEncoder,
        ResetPasswordMailer $resetPasswordMailer,
        ApiTokenRepository $apiTokenRepo,
        SecurityMailer $securityMailer,
        UploaderHelper $uploaderHelper,
        ImageCacheGenerator $imageCacheGenerator,
        CacheManager $cacheManager
    ) {
        $this->entityManager = $entityManager;
        $this->assetsManager = $assetsManager;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->validator = $validator;
        $this->guardHandler = $guardHandler;
        $this->authenticator = $authenticator;
        $this->twig = $twig;
        $this->securityToken = $securityToken;
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
        $this->resetPasswordMailer = $resetPasswordMailer;
        $this->apiTokenRepo = $apiTokenRepo;
        $this->securityMailer = $securityMailer;
        $this->uploaderHelper = $uploaderHelper;
        $this->imageCacheGenerator = $imageCacheGenerator;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns the site url
     * @return string
     */
    public function getFullQualifiedBaseUrl() {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();
        return sprintf('%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'. $port : ''),
            $routerContext->getBaseUrl()
        );
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }
}
