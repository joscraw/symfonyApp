<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyPhoto;
use App\Entity\CompanyResource;
use App\Entity\Image;
use App\Entity\Lesson;
use App\Entity\LessonTeachable;
use App\Entity\ProfessionalUser;
use App\Entity\SiteAdminUser;
use App\Entity\StateCoordinator;
use App\Entity\User;
use App\Form\EditCompanyFormType;
use App\Form\NewCompanyFormType;
use App\Form\NewLessonType;
use App\Form\ProfessionalEditProfileFormType;
use App\Form\SiteAdminFormType;
use App\Form\StateCoordinatorFormType;
use App\Mailer\RequestsMailer;
use App\Mailer\SecurityMailer;
use App\Repository\CompanyPhotoRepository;
use App\Repository\CompanyRepository;
use App\Repository\LessonFavoriteRepository;
use App\Repository\LessonTeachableRepository;
use App\Repository\StateCoordinatorRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\ImageCacheGenerator;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\RandomStringGenerator;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/dashboard/admin")
 */
class AdminController extends AbstractController
{
    use ServiceHelper;

    /**
     * @IsGranted("ROLE_ADMIN_USER")
     * @Route("/", name="organization_new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newOrganization(Request $request) {


        return new Response("test");

        $user = $this->getUser();
        $siteAdmin = new SiteAdminUser();

        $form = $this->createForm(SiteAdminFormType::class, $siteAdmin, [
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var SiteAdminUser $siteAdmin */
            $siteAdmin = $form->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $siteAdmin->getEmail()]);
            // for now just skip users that are already in the system
            if($existingUser) {
                $this->addFlash('error', 'This user already exists in the system');
                return $this->redirectToRoute('sites_admin_new');
            } else {
                $siteAdmin->initializeNewUser(false, true);
                $siteAdmin->setPasswordResetToken();
                $siteAdmin->setupAsSiteAdminUser();
                $this->entityManager->persist($siteAdmin);
            }

            $this->entityManager->flush();
            $this->securityMailer->sendPasswordSetupForSiteAdmin($siteAdmin);
            $this->addFlash('success', 'Site admin invite sent.');
            return $this->redirectToRoute('sites_admin_new');
        }

        return $this->render('site/newSiteAdmin.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}