<?php

namespace AppBundle\Controller;
use AppBundle\Repository\KddsJSONRepository;
use AppBundle\Entity\KddsTblBaby;
use AppBundle\Entity\KddsTblBabyAttachment;
use AppBundle\Entity\KddsTblBabyPlate;
use AppBundle\Entity\KddsTblManuscript;
use AppBundle\Entity\KddsTblBabyVersion;
use AppBundle\Form\ManuscriptType;
use AppBundle\Form\PlateType;
use AppBundle\Form\BabyType;
use AppBundle\Form\VersionType;
use AppBundle\Form\AttachmentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class BabyController
 * @package AppBundle\Controller
 * @Route("/baby")
 * @Security("has_role('ROLE_GUEST') or has_role('ROLE_USER')")
 */
class BabyController extends Controller
{
    /**
     * @Route("/search", name="baby_search")
     */
    public function babySearchAction(Request $request) {

        try {
            $jsonCondition = json_decode($request->request->get('jsonCondition'));
            $isCollapse = 'true';
            if ($request->request->get('isCollapse') !== null) {
                $isCollapse = $request->request->get('isCollapse');
            }
            //
            $em = $this->getDoctrine()->getManager();
            $formatter = $em->getRepository("AppBundle:KddsMstFormatter")->getDataView();
            $composition = $em->getRepository("AppBundle:KddsMstComposition")->getDataView();
            $expression = $em->getRepository("AppBundle:KddsMstExpression")->getDataView();
            $printingProcess = $em->getRepository("AppBundle:KddsMstPrintingProcess")->getDataView();
            $useIndustry = $em->getRepository("AppBundle:KddsMstUseIndustry")->getDataView();
            $pattern = $em->getRepository("AppBundle:KddsMstPattern")->getDataView();
            //
            return $this->render('default/baby_search.html.twig', [
                'jsonCondition' => $jsonCondition,
                'formatterList' => $formatter,
                'compositionList' => $composition,
                'expressionList' => $expression,
                'printingProcessList' => $printingProcess,
                'useIndustryList' => $useIndustry,
                'patternList' => $pattern,
                'isCollapse' => $isCollapse,
            ]);
        } catch(\Exception $e) {
            //エラー内容をログファイルに書き込む。
            $logger = $this->get('logger');
            $logger->critical($e->getMessage());
            //
            return $this->render('default/baby_search.html.twig', [
                'exception' => $this->getParameter('exception_message'),
            ]);
        }
    }

    /**
     * @Route("/search_ajax", name="baby_search_ajax")
     * @Method ("POST")
     */
    public function babySearchAjaxAction(Request $request) {

        $response = new JsonResponse();
        try {
            $parameters = $request->request->all();
            //$parameters = array_filter($parameters);                                 // 20170915 FJN PHUC-PT KDDS-94 DEL
            $parameters = array_filter($parameters, function ($v){return $v !== '';}); // 20170915 FJN PHUC-PT KDDS-94 ADD
            $parameters = KddsJSONRepository::convertDateInParameter($parameters);
            $arrayData  = KddsJSONRepository::convert_filter_array($parameters);
            $parametersData = $arrayData['parametersData'];
            $parametersLink = $arrayData['parametersLink'];
            $em = $this->getDoctrine()->getManager();
            $query = $em->getRepository("AppBundle:KddsTblBaby")
                        ->getQueryBabyByRequest($parametersData, $this->getUser());
            $page          = isset($parameters['page'])?$parameters['page']:1;
            $itemPerPage   = isset($parameters['itemPerPage'])?$parameters['itemPerPage']:20;
            $pagination    = $this->get('knp_paginator');
            $babyRecords   = $pagination->paginate(
                $query,
                $page,
                $itemPerPage
            );

            $babyRecords->setUsedRoute('baby_result');
            foreach ($parametersLink as $key => $value) {
                $babyRecords->setParam($key, $value);
            }
            $totalItems = count($babyRecords);
            if (0 < $totalItems) {
                $this->get('session')->set('babyRecords', $babyRecords);
                $responseJSON = KddsJSONRepository::kdds_json_encode($parametersData);
                $em->getRepository("AppBundle:KddsTblSearchHistory")->addNewHistory(
                    $this->getUser()->getUsername(),
                    $responseJSON,
                    1);

            }
            $response->setData(array(
                'resultLink' => $this->generateUrl('baby_result', $parametersLink),
                'total' => $totalItems,
                'error' => '',
            ));
            //
            return $response;
        } catch(\Exception $e) {
            //エラー内容をログファイルに書き込む。
            $logger = $this->get('logger');
            $logger->critical($e->getMessage());
            //
            $response->setData(array(
                'resultLink' => '',
                'total' => 0,
                'error' => $this->getParameter('exception_message'),
            ));
            return $response;
        }
    }
