<?php

namespace App\Controller\Api;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UploaderHelper;
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
 * Class ImageController
 * @package App\Controller
 * @Route("/api/images")
 */
class ImageController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/add", name="image_add", options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addImageAction(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UploadedFile $uploadedFile */
        $photo = $request->files->get('file');
        if($photo) {
            $newFilename = $this->uploaderHelper->upload($photo, UploaderHelper::CLINIC_LOGO);
            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::CLINIC_LOGO) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);
            /*$user->setPhoto($newFilename);
            $this->entityManager->persist($user);
            $this->entityManager->flush();*/
            return new JsonResponse(
                [
                    'success' => true,
                    'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::CLINIC_LOGO.'/'.$newFilename, 'squared_thumbnail_small'),
                    'path' => $path,
                    'message' => 'Jack, make sure you pass up the path value to the objects when saving. The url value is more or less used to just display the image after the upload.'
                ], Response::HTTP_OK
            );
        }
        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }
}