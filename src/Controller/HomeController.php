<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Form\MusicImportType;
use App\Util\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class HomeController
 * @package App\Controller
 * @Route("/dashboard/home")
 */
class HomeController extends AbstractController
{
    use ServiceHelper;

    /**
     * @IsGranted({"ROLE_ADMIN_USER", "ROLE_USER"})
     * @Route("/", name="home_index", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function indexAction(Request $request) {

        return new Response("home controller");
    }
}