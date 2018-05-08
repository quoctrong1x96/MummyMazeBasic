  BUG 405 nha. GET THÌ vẫn chạy bình thường
  mấy cái getdata trong code khỏi quan tâm. nhưng không biết sao chạy sai
  ------------SERVER 
 
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
    ------------------------\ CLIENT
    <!DOCTYPE html>
<html lang="ja">
<head>
    <title>
        {{ image_url | split('/') | last }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
    <!-- 20180405 FJN DAN-TN ADD -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        .table {
            display: table;
            height: calc(100% - 16px);
            width: calc(100% - 16px);
            position: absolute;
            user-select: none;
        }
        .table-cell {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        .div-btn{
            float: right;
            width: 180px;
        }
        .btn-background{
            background: #031da3
        }
        .btn-collapse {
            background: #031da3;
            color: #FFFFFF;
            height: 40px;
            text-align: center;
            line-height: 0;
            border-top-right-radius: 0em;
            border-top-left-radius: 0em;

        }
        .btn-collapse:focus,
        .btn-collapse:hover {
            color: #FFFFFF;
            background-color: #031da3;
            box-shadow: 0 5px 15px #888888;
        }
        .btn-collapse:active {
            box-shadow: inset 0 0 0 1px #031da3,inset 0 5px 30px #031da3;
        }
    </style>
</head>
<body>
    <!-- 20180405 FJN DAN-TN ADD START col-md-1 col-md-offset-9-->
   {# <form id="formRoleSearch" action="{{ path('role_search_ajax') }}" method="POST">#}
    <div class="row">
        <div class="div-btn">
            <div class="container-fluid">
                <div class="form-group">            
                    <button id="btnNext" type="submit" class="btn btn-default btn-background btn-collapse form-control">
                        次へ&emsp;
                        <span class="pull-right">
                        <span class="glyphicon glyphicon-arrow-right">
                        </span>
                        </span>
                    </button>            
                </div>
            </div>
        </div>
        <div class="div-btn">
            <div class="container-fluid">
                <div class="form-group">
                    <button id="btnBack" type="submit" class="btn btn-default btn-background btn-collapse form-control">
                        &emsp;前へ
                        <span class="pull-left">
                        <span class="glyphicon glyphicon-arrow-left">
                        </span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    {% for babyRecord in babyRecordList %}
        <tr>
            {% set detail_link = path('baby_detail', {babyPatternNo: babyRecord[0].babyPatternNo|trim(' ', 'right'),
                'search_params': app.request.query.all}) %}
            <td>
                <label class="form-group product-label">{{ babyRecord[0].babyPatternNo|trim(' ', 'right') }}</label>
            </td>
        </tr>
    {% endfor %}
   {# </form>#}
    <!-- 20180405 FJN DAN-TN ADD END -->
    <div class="table">
        <div class="table-cell">
            {#<img src="{{ path('common_s3_get_image', {'id':formatS3FilePath(image_url)}) }}"/>#}
            <img src="{{ asset('images/PHP.png') }}"/>
        </div>
    </div>
</body>
<!-- 20180405 FJN DAN-TN ADD START -->
<script src="{{ asset('js/show-images.js') }}"></script>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
         $(document).ready(function() {
             var image_url = null;
             var babyRecordList = null;
             var sortOrder = null;
             var itemPerPage = null;
             var thumbnail = null;
             var ROOT_URL = location.href;
             $.ajax({
                 type: 'POST',
                 url: ROOT_URL,
                 success: function (response) {
                     console.log(response);
                     console.log(response.image_url);
                 },
                 error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(textStatus);
                    console.log(errorThrown);
                 }
             });
         });

</script>
<!-- 20180405 FJN DAN-TN ADD END -->
</html>
----
