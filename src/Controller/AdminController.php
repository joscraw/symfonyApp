<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ManageUserFilterType;
use App\Repository\UserRepository;
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
     * @Route("/users", name="admin_users", options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function users(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ManageUserFilterType::class, null, [
            'action' => $this->generateUrl('admin_users'),
            'method' => 'GET'
        ]);

        $form->handleRequest($request);

        $filterBuilder = $this->userRepository->createQueryBuilder('u');

        if ($form->isSubmitted() && $form->isValid()) {
            // build the query from the given form object
            $this->filterBuilder->addFilterConditions($form, $filterBuilder);
        }

        $filterQuery = $filterBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $filterQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('admin/manage_users.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'form' => $form->createView(),
            'clearFormUrl' => $this->generateUrl('admin_users')
        ]);
    }
}