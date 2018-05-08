<?php

namespace AppBundle\Controller;
use AppBundle\Repository\KddsJSONRepository;                     // 20180504 FJN DAN-TD ADD
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;            // 20170904 FJN PHUC-PT KDDS-85
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // 20170904 FJN PHUC-PT KDDS-85

/**
 * Class CommonController
 * @package AppBundle\Controller
 * @Route("/common")
 */
class CommonController extends Controller
{
    /**
     * @Route("/show_image", name="common_show_image")
     * @Security("has_role('ROLE_GUEST') or has_role('ROLE_USER')")
     * @Method({"GET"})
     */
    public function showImageAction(Request $request){
        // 20180504 FJN DAN-TD ADD START
            $thumbnail   = 'false';
        try {
            $parameters = $request->query->all();
            $sort       = isset($parameters['sort'])?$parameters['sort']:null;

            $session = $this->get('session');
            $babyRecords = $session->get('babyRecords');
            $em = $this->getDoctrine()->getManager();
            if ($babyRecords !== null) {
                $session->remove('babyRecords');
            } else {
                $parameters['isCollapse'] = 'false';
                $parameters = KddsJSONRepository::convertDateInParameter($parameters);
                $arrayData = KddsJSONRepository::convert_filter_array($parameters);
                $parametersData = $arrayData['parametersData'];
                $query = $em->getRepository("AppBundle:KddsTblBaby")
                    ->getQueryBabyByRequest($parametersData, $this->getUser());
                $thumbnail     = isset($parameters['thumbnail'])?$parameters['thumbnail']:'false';
                if('true' != $thumbnail) {
                    $thumbnail = 'false';
                } else {
                    $thumbnail = 'true';
                };

                $pagination  = $this->get('knp_paginator');
                $babyRecords = $pagination->paginate(
                    $query,
                    1,
                    999999
                );
            }
            $image_url = $request->get('image_url');
            return $this->render('default/common_show_image.html.twig', [
                'image_url' => $image_url,
                'babyRecordList'     => $babyRecords,
                'sortOrder'       => $sort,
                'itemPerPage'     => $itemPerPage,
                'thumbnail'       => $thumbnail,
            ]);
        } catch(\Exception $e) {
            //エラー内容をログファイルに書き込む。
            $logger = $this->get('logger');
            $logger->critical($e->getMessage());
            //
            return $this->render('default/common_show_image.html.twig', [
                'exception' => $this->getParameter('exception_message'),
            ]);
        }
        // 20180504 FJN DAN-TD DEL START

        // 20180504 FJN DAN-TD ADD START
        /*$image_url = $request->get('image_url');
        return $this->render('default/common_show_image.html.twig', [
            'image_url' => $image_url
        ]);*/
        // 20180504 FJN DAN-TD DEL START
    }
    // 20180504 FJN DAN-TD ADD START
    /**
     * @Route("/show_image", name="common_show_image")
     * @Security("has_role('ROLE_GUEST') or has_role('ROLE_USER')")
     * @Method({"POST"})
     */
    public function showImageAjaxAction(Request $request){
        // 20180504 FJN DAN-TD ADD START
        $response = new JsonResponse();
        try {
            $parameters = $request->query->all();

            $session = $this->get('session');
            $babyRecords = $session->get('babyRecords');
            $em = $this->getDoctrine()->getManager();
            if ($babyRecords !== null) {
                $session->remove('babyRecords');
            } else {
                $parameters['isCollapse'] = 'false';
                $parameters = KddsJSONRepository::convertDateInParameter($parameters);
                $arrayData = KddsJSONRepository::convert_filter_array($parameters);
                $parametersData = $arrayData['parametersData'];
                $query = $em->getRepository("AppBundle:KddsTblBaby")
                    ->getQueryBabyByRequest($parametersData, $this->getUser());

                $pagination  = $this->get('knp_paginator');
                $babyRecords = $pagination->paginate(
                    $query,
                    1,
                    999999
                );
            }
            $image_url = $request->get('image_url');
            $response->setData(array(
                'babyRecordList' => $babyRecords,
                'image_url' => $image_url,
                'error' => '',
            ));

            return $response;
        } catch(\Exception $e) {
            //エラー内容をログファイルに書き込む。
            $logger = $this->get('logger');
            $logger->critical($e->getMessage());
            //
            $response->setData(array(
                'babyRecordList' => '',
                'image_url' => '',
                'error' => $this->getParameter('exception_message'),
            ));
            return $response;
        }
        // 20180504 FJN DAN-TD ADD END
    }
}
