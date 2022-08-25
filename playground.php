<?php 
require 'breed_info.php';
global $current_dog_gender; //+++
$current_dog_gender = '1'; //+++
$dates = array("2022-03-01","2022-03-15","2022-04-01","2022-05-01","2022-05-28"); //+++
//$dates = array("2022-05-03");
$weights = array("7.2","20","30","35","38");//+++
//$weights = array("39");//+++
//global $standard_centiles;
//global $latest_weight;
//global $latest_date;
$current_dog_breed = '29';
$pure_dob = '2022-01-01';//+++

$weight_group = get_breed_weight_group($current_dog_breed,$current_dog_gender,$weights,$dates,$pure_dob);

function get_breed_weight_group($current_breed_name,$current_dog_gender,$weights,$dates,$pure_dob){
    require 'breed_info.php';

    foreach ($breed_info as $breed=>$info){
        if ($info[0] == $current_breed_name){ #$current_breed_name is acutally breed_id, but we use name to continue supporting early naming
            //echo 'weight group: '.$info[1].'<br>';
            $weight_group = $info[1];
            break;
            
        }
    }
    if ($weight_group == 'auto'){
        //get the smallest weight
        //find the centile it belongs

        //it will big the group that the target weight falls in the middle of its centile set of lines
        // it is biased towards upper groups cos it searches for the smallest group to the biggest, override the previous

        //echo('<p>Weight Group is Auto</p>');
        $smallest_weight = $weights[0];
        $smallest_date = $dates[0];

        //echo ('Smallest Weight is: '.$smallest_weight.'<br><br>');

        $smallest_date_dt = new DateTime($smallest_date);
        $dob_dt = new DateTime($pure_dob);
        $smallest_age = $dob_dt->diff($smallest_date_dt);  #latest age based relative to the last time the dog was weighed.
        $smallest_age_in_weeks = ($smallest_age->days)/7.0;

        $weight_groups = array('i','ii','iii','iv','v');

        $min_centile_lines_and_groups = array(); // array that contains the centile line that is closes to the smallest user recorded weight, and the group the centile belongs
        foreach ($weight_groups as $weight_group){

            if ($current_dog_gender == '1'){
                $temp_centiles = generate_female_centile($smallest_age_in_weeks,$weight_group);
            }
            if ($current_dog_gender == '2'){
                    $temp_centiles = generate_male_centile($smallest_age_in_weeks,$weight_group);
            }


            $min_diff_weight = 1000000; // a very large number
            $min_group = '0';

            //echo('<p>Temp Centile </p>'.$weight_group.':   ');
            //print_r($temp_centiles);
            //print_r('<p></p>');

            foreach ($temp_centiles as $key=>$temp_centile_weight){
                $temp_diff_weight = abs((float)$temp_centile_weight - $smallest_weight);

                if ($temp_diff_weight < $min_diff_weight){

                    $min_diff_weight = $temp_diff_weight;
                    $min_centile_id = $key;

                }
            }

            $min_centile_lines_and_groups[] = array($weight_group,$min_centile_id);
            
        }
        //echo('<p>$min_centile_lines_and_groups</p>');
        //print_r($min_centile_lines_and_groups) ; 

        $too_big_dog = true;
        $too_small_dog = true;

        foreach ($min_centile_lines_and_groups as $value_array){

            if ($value_array[1] == 8){
                //echo ('<p>Centile id is '.$value_array[1].'</p>');
                $too_big_dog = true;
            }
            else{
                //echo ('<p>Centile id is '.$value_array[1].'</p>');
                //echo('<p>Not Too Big Dog</p>');
                $too_big_dog = false;
                break;
            }
        }

 
        foreach ($min_centile_lines_and_groups as $value_array){

            if ($value_array[1] == 0){
                //echo ('<p>Centile id is '.$value_array[1].'</p>');
                $too_small_dog = true;
            }
            else{
                //echo ('<p>Centile id is '.$value_array[1].'</p>');
                $too_small_dog = false;
                //echo('<p>Not Too Small Dog</p>');
                break;
            }
        }
        
        if (!$too_big_dog){

            if (!$too_small_dog){


                //echo('<p>Not Too Big or Too Small Dog</p>');
                $min_dev = 8;
                foreach ($min_centile_lines_and_groups as $value_array){
                    $dev_from_mid = abs($value_array[1] - 4); // centile with index 4 is the middle if the group of 9 centile curves. auto should be the once closest to middle of index 4
                    if ($dev_from_mid <= $min_dev){
                        $min_dev = $dev_from_mid; 
                        $auto_group = $value_array[0];
                        //echo 'auto group is :'.$auto_group.'<br><br>';
                    }
                }
            }
            else{
                //echo '<p>your dog is not too big BUT it is TOO small</p>';  
                $auto_group = 'i'; //use smallest group for smallest dog
            }

        }
        else{
            //echo '<p>your dog is TOO big</p>';
            $auto_group = 'v'; //use biggest group for too big dog
        }
        $weight_group = $auto_group;
      
    }
    

    //echo ('<p>.</p>');
    //echo ('<p>.</p>');
    //echo ('<p>.</p>');
    return $weight_group;

}



function generate_female_centile($A1,$weight_group){
    $KG_TO_POUND = 2.20462;

    if ($weight_group == 'i'){
        $female_centile  = array(
            strval((-4.4074756345118106e-08*$A1**4 + 1.2305395947901555e-05*$A1**3 + -0.0012326807095228685*$A1**2 + 0.055925483760825706*$A1 + 0.06710071448816109)*$KG_TO_POUND),
            strval((-5.24251950796956e-08*$A1**4 + 1.4781805392679927e-05*$A1**3 + -0.0015115967812496956*$A1**2 + 0.07202551839869203*$A1 + 0.028142609756824904)*$KG_TO_POUND),
            strval((-6.340776845375918e-08*$A1**4 + 1.8092151698828912e-05*$A1**3 + -0.0018885445034178925*$A1**2 + 0.09305500186370029*$A1 + -0.022041726447744027)*$KG_TO_POUND),
            strval((-7.796970289237716e-08*$A1**4 + 2.2432048887550232e-05*$A1**3 + -0.0023749910194794588*$A1**2 + 0.1187597696535581*$A1 + -0.07774244852069756)*$KG_TO_POUND),
            strval((-9.708864628176203e-08*$A1**4 + 2.8083143424766782e-05*$A1**3 + -0.0029973560721984994*$A1**2 + 0.14988772713071422*$A1 + -0.13358730966662188)*$KG_TO_POUND),
            strval((-1.2235254720996704e-07*$A1**4 + 3.541805181977615e-05*$A1**3 + -0.0037841814722317873*$A1**2 + 0.18713494311660323*$A1 + -0.18259941885881334)*$KG_TO_POUND),
            strval((-1.5480937339208506e-07*$A1**4 + 4.4711689990537504e-05*$A1**3 + -0.004758482260200478*$A1**2 + 0.2311092217338494*$A1 + -0.21630804046722893)*$KG_TO_POUND),
            strval((-1.9638841687313313e-07*$A1**4 + 5.6379502761873586e-05*$A1**3 + -0.00594831527512961*$A1**2 + 0.28219252403326744*$A1 + -0.21206711872485623)*$KG_TO_POUND),
            strval((-2.432246025565321e-07*$A1**4 + 6.944774155842148e-05*$A1**3 + -0.00725992295864128*$A1**2 + 0.33618516468298965*$A1 + -0.14322762977041442)*$KG_TO_POUND),

        );

        return $female_centile;

    }
    if ($weight_group == 'ii'){
        $female_centile  = array(

            strval((-9.978963967850019e-08*$A1**4 + 2.934015416780629e-05*$A1**3 + -0.003177306134311099*$A1**2 + 0.15713508790567818*$A1 + -0.4200810779486984)*$KG_TO_POUND),
            strval((-1.1944119767319057e-07*$A1**4 + 3.511232100260946e-05*$A1**3 + -0.0038053644073070504*$A1**2 + 0.18920442174454605*$A1 + -0.530198762171803)*$KG_TO_POUND),
            strval((-1.4445634222775356e-07*$A1**4 + 4.242998687552153e-05*$A1**3 + -0.00459539181230675*$A1**2 + 0.22855330419307596*$A1 + -0.6546249055939511)*$KG_TO_POUND),
            strval((-1.7545164032651645e-07*$A1**4 + 5.138048093991833e-05*$A1**3 + -0.005546088103834222*$A1**2 + 0.2742750312592372*$A1 + -0.7851958525019784)*$KG_TO_POUND),
            strval((-2.1161631274145003e-07*$A1**4 + 6.184934823796564e-05*$A1**3 + -0.006653293530072816*$A1**2 + 0.32613186018737345*$A1 + -0.9010772088336825)*$KG_TO_POUND),
            strval((-2.582654236982509e-07*$A1**4 + 7.499395972030023e-05*$A1**3 + -0.008003567780897287*$A1**2 + 0.3869173846854126*$A1 + -1.029683254977036)*$KG_TO_POUND),
            strval((-3.081695281230082e-07*$A1**4 + 8.923840829630755e-05*$A1**3 + -0.009482024072757214*$A1**2 + 0.45384481526109743*$A1 + -1.154510556573074)*$KG_TO_POUND),
            strval((-3.6834627591711807e-07*$A1**4 + 0.0001061057662258448*$A1**3 + -0.01120092560523594*$A1**2 + 0.5301110505677816*$A1 + -1.2877474506223259)*$KG_TO_POUND),
            strval((-4.3370457549171946e-07*$A1**4 + 0.0001243522542235659*$A1**3 + -0.013049449319262045*$A1**2 + 0.6112268805184105*$A1 + -1.4224680542030264)*$KG_TO_POUND),
        );

        return $female_centile;

    }

    if ($weight_group == 'iii'){
        $female_centile  = array(

            strval((-1.0449066057120637e-07*$A1**4 + 3.139432385791183e-05*$A1**3 + -0.003533253940405036*$A1**2 + 0.1882351600909442*$A1 + -0.5849830628883589)*$KG_TO_POUND),
            strval((-1.344641398863077e-07*$A1**4 + 4.067023408521903e-05*$A1**3 + -0.004632025261639562*$A1**2 + 0.2531256188717185*$A1 + -0.847405830865011)*$KG_TO_POUND),
            strval((-1.766709641656515e-07*$A1**4 + 5.336272126847749e-05*$A1**3 + -0.006070567224632982*$A1**2 + 0.3304442540032387*$A1 + -1.1355955213493931)*$KG_TO_POUND),
            strval((-2.2731476296016707e-07*$A1**4 + 6.827239540626703e-05*$A1**3 + -0.007711117268709692*$A1**2 + 0.4135870482314234*$A1 + -1.3978822071347516)*$KG_TO_POUND),
            strval((-2.857623488159364e-07*$A1**4 + 8.530372473461845e-05*$A1**3 + -0.009549067951410467*$A1**2 + 0.5027602479420795*$A1 + -1.625502893071502)*$KG_TO_POUND),
            strval((-3.507707570697496e-07*$A1**4 + 0.00010393859344110796*$A1**3 + -0.011511923881283736*$A1**2 + 0.5937583292946568*$A1 + -1.7986672679857485)*$KG_TO_POUND),
            strval((-4.22773888654803e-07*$A1**4 + 0.0001239843356776055*$A1**3 + -0.013550299670814904*$A1**2 + 0.6833935211213504*$A1 + -1.9204522977045477)*$KG_TO_POUND),
            strval((-4.892962062329948e-07*$A1**4 + 0.00014279226574002133*$A1**3 + -0.015475982430840671*$A1**2 + 0.7669100150494212*$A1 + -1.944994617300258)*$KG_TO_POUND),
            strval((-5.565718118174315e-07*$A1**4 + 0.00016135601482465429*$A1**3 + -0.017324816886613623*$A1**2 + 0.8440183830393772*$A1 + -1.9140674582156452)*$KG_TO_POUND),
            
        );

        return $female_centile;

    }

    if ($weight_group == 'iv'){
        $female_centile  = array(

            strval((-4.5036836985733683e-07*$A1**4 + 0.0001390331884802661*$A1**3 + -0.015958952577293285*$A1**2 + 0.8388851278666765*$A1 + -5.238178702229235)*$KG_TO_POUND),
            strval((-5.65891047794561e-07*$A1**4 + 0.00017434224297697916*$A1**3 + -0.019943087355106796*$A1**2 + 1.0309863539749098*$A1 + -6.12535873474034)*$KG_TO_POUND),
            strval((-6.929804958207026e-07*$A1**4 + 0.00021225794377004493*$A1**3 + -0.024117412870983734*$A1**2 + 1.2296449454204001*$A1 + -6.999323893920028)*$KG_TO_POUND),
            strval((-8.08331702435267e-07*$A1**4 + 0.00024685493428950814*$A1**3 + -0.027939983360880383*$A1**2 + 1.4155434886362046*$A1 + -7.765943318705221)*$KG_TO_POUND),
            strval((-9.230504647702174e-07*$A1**4 + 0.0002808431686873971*$A1**3 + -0.031654746277531405*$A1**2 + 1.5991763022698884*$A1 + -8.564150147761172)*$KG_TO_POUND),
            strval((-1.0378112371338154e-06*$A1**4 + 0.00031458217768152573*$A1**3 + -0.035315071656146176*$A1**2 + 1.776921926692786*$A1 + -9.234930727888052)*$KG_TO_POUND),
            strval((-1.1694638808637509e-06*$A1**4 + 0.0003529540037988989*$A1**3 + -0.03938773449713667*$A1**2 + 1.9602865549190829*$A1 + -9.793734609074912)*$KG_TO_POUND),
            strval((-1.3225789907744368e-06*$A1**4 + 0.00039671869099639027*$A1**3 + -0.04390182328659387*$A1**2 + 2.1507360333556815*$A1 + -10.261130557517493)*$KG_TO_POUND),
            strval((-1.4628594868240127e-06*$A1**4 + 0.00043688990663344083*$A1**3 + -0.048022732586180956*$A1**2 + 2.3187080957087534*$A1 + -10.428936395692398)*$KG_TO_POUND),
        );

        return $female_centile;

    }
    if ($weight_group == 'v'){
        $female_centile  = array(

            strval((-4.162472964459126e-07*$A1**4 + 0.00014351552148526977*$A1**3 + -0.018412471267734085*$A1**2 + 1.092511755105643*$A1 + -7.295809431392749)*$KG_TO_POUND),
            strval((-6.157262417750397e-07*$A1**4 + 0.00020101156616580713*$A1**3 + -0.024393101469934855*$A1**2 + 1.3530268219356305*$A1 + -8.622798818412896)*$KG_TO_POUND),
            strval((-8.111536552686968e-07*$A1**4 + 0.0002571070511932355*$A1**3 + -0.030207400110859952*$A1**2 + 1.605805865528796*$A1 + -9.749514983228321)*$KG_TO_POUND),
            strval((-9.924275722706902e-07*$A1**4 + 0.0003089461175640749*$A1**3 + -0.03555193928524601*$A1**2 + 1.8366352375875508*$A1 + -10.623972992090374)*$KG_TO_POUND),
            strval((-1.1577494655980003e-06*$A1**4 + 0.00035613582183182334*$A1**3 + -0.040405001190876505*$A1**2 + 2.045484274089985*$A1 + -11.255036864223733)*$KG_TO_POUND),
            strval((-1.3149689604359214e-06*$A1**4 + 0.0004008264835129672*$A1**3 + -0.04498640349067791*$A1**2 + 2.242706943453716*$A1 + -11.712687342468255)*$KG_TO_POUND),
            strval((-1.469844516154361e-06*$A1**4 + 0.00044472869064992454*$A1**3 + -0.04948362241792272*$A1**2 + 2.4379249083109635*$A1 + -12.062440426560512)*$KG_TO_POUND),
            strval((-1.6106271780846868e-06*$A1**4 + 0.0004853123958297009*$A1**3 + -0.053714923803166476*$A1**2 + 2.6261820430161427*$A1 + -12.253693720449535)*$KG_TO_POUND),
            strval((-1.742290541990522e-06*$A1**4 + 0.0005230902608152143*$A1**3 + -0.05763866451478392*$A1**2 + 2.8015652150328543*$A1 + -12.32867708414846)*$KG_TO_POUND),
        );

        return $female_centile;

    }


    $female_centile  = array(    
        strval((-4.46382E-07*$A1**4 + 1.39075E-04*$A1**3 - 1.60717E-02*$A1**2 + 8.46252E-01*$A1 - 5.25734E+00)*$KG_TO_POUND),
        strval((-5.24782E-07*$A1**4 + 1.67019E-04*$A1**3 - 1.95896E-02*$A1**2 + 1.02956*$A1 - 6.23807E+00)*$KG_TO_POUND),
        strval((-6.83450E-07*$A1**4 + 2.11386E-04*$A1**3 - 2.41724E-02*$A1**2 + 1.23737*$A1 - 7.05894E+00)*$KG_TO_POUND),
        strval((-8.44414E-07*$A1**4 + 2.56507E-04*$A1**3 - 2.88361E-02*$A1**2 + 1.44558*$A1 - 7.84942E+00)*$KG_TO_POUND),
        strval((-9.92385E-07*$A1**4 + 2.96114E-04*$A1**3 - 3.28223E-02*$A1**2 + 1.63155*$A1 - 8.52572E+00)*$KG_TO_POUND),
        strval((-9.41425E-07*$A1**4 + 2.91631E-04*$A1**3 - 3.32863E-02*$A1**2 + 1.69321*$A1 - 7.81285E+00)*$KG_TO_POUND),
        strval((-9.86910E-07*$A1**4 + 3.10447E-04*$A1**3- 3.61898E-02*$A1**2 + 1.87861*$A1 - 9.32235E+00)*$KG_TO_POUND),
        strval((-1.14237E-06*$A1**4 + 3.58886E-04*$A1**3 - 4.15840E-02*$A1**2 + 2.12968*$A1 - 1.14166E+01)*$KG_TO_POUND),
        strval((-1.15426E-06*$A1**4 + 3.68138E-04*$A1**3 - 4.31657E-02*$A1**2 + 2.21395*$A1 - 1.06269E+01)*$KG_TO_POUND)
    );

    return $female_centile;

}


function generate_male_centile($A1,$weight_group){
    $KG_TO_POUND = 2.20462;

    if ($weight_group == 'i'){
        $male_centile  = array(
            strval((-6.355685040143598e-08*$A1**4 + 1.7882201489443358e-05*$A1**3 + -0.0018041536557943055*$A1**2 + 0.07969482387374086*$A1 + -0.11350166682989325)*$KG_TO_POUND),
            strval((-7.426674034759059e-08*$A1**4 + 2.103025031105247e-05*$A1**3 + -0.002151761993554347*$A1**2 + 0.09889943152585938*$A1 + -0.17824966257693126)*$KG_TO_POUND),
            strval((-8.728549585844358e-08*$A1**4 + 2.4950005265488752e-05*$A1**3 + -0.0025982325581248742*$A1**2 + 0.12371338019781244*$A1 + -0.2606286263051197)*$KG_TO_POUND),
            strval((-1.0222998072184242e-07*$A1**4 + 2.9617999665100703e-05*$A1**3 + -0.003147950663352612*$A1**2 + 0.154242019076496*$A1 + -0.3583876369850566)*$KG_TO_POUND),
            strval((-1.2280715637826644e-07*$A1**4 + 3.597590872572991e-05*$A1**3 + -0.003887895573805325*$A1**2 + 0.19379149032812326*$A1 + -0.4903227161954598)*$KG_TO_POUND),
            strval((-1.4860476276340794e-07*$A1**4 + 4.395966306819327e-05*$A1**3 + -0.004809116243256967*$A1**2 + 0.24100059283229586*$A1 + -0.6248438453060827)*$KG_TO_POUND),
            strval((-1.8251905710699417e-07*$A1**4 + 5.4133426178350556e-05*$A1**3 + -0.005934275335781073*$A1**2 + 0.29453450645558626*$A1 + -0.7295471754533068)*$KG_TO_POUND),
            strval((-2.236202633839271e-07*$A1**4 + 6.625333940628841e-05*$A1**3 + -0.007235260574272383*$A1**2 + 0.3527292993901637*$A1 + -0.7612628061263089)*$KG_TO_POUND),
            strval((-2.700005156700918e-07*$A1**4 + 7.958184116669345e-05*$A1**3 + -0.008615527956702632*$A1**2 + 0.410571814187135*$A1 + -0.683436070606282)*$KG_TO_POUND),

        );

        return $male_centile;

    }
    if ($weight_group == 'ii'){
        $male_centile  = array(

            strval((-1.266603727939994e-07*$A1**4 + 3.761550325707627e-05*$A1**3 + -0.004123139738807433*$A1**2 + 0.20470326528032018*$A1 + -0.8508115128526672)*$KG_TO_POUND),
            strval((-1.5228994884910608e-07*$A1**4 + 4.517332653587797e-05*$A1**3 + -0.004949990664460946*$A1**2 + 0.24684650484362564*$A1 + -1.0185837559289226)*$KG_TO_POUND),
            strval((-1.8605217556225036e-07*$A1**4 + 5.4929769042536876e-05*$A1**3 + -0.005992212897733225*$A1**2 + 0.2977115325548093*$A1 + -1.2022018719021585)*$KG_TO_POUND),
            strval((-2.253643834305567e-07*$A1**4 + 6.621494798432248e-05*$A1**3 + -0.007181950013134784*$A1**2 + 0.3536627825267436*$A1 + -1.3695610480754723)*$KG_TO_POUND),
            strval((-2.7249276181238277e-07*$A1**4 + 7.954570903954138e-05*$A1**3 + -0.008556563646420788*$A1**2 + 0.41531278469151417*$A1 + -1.5110926033800463)*$KG_TO_POUND),
            strval((-3.2732220705155403e-07*$A1**4 + 9.490624320848942e-05*$A1**3 + -0.010118170007523622*$A1**2 + 0.4834779920207695*$A1 + -1.6309304976746242)*$KG_TO_POUND),
            strval((-3.927572671044752e-07*$A1**4 + 0.0001129693656580642*$A1**3 + -0.011927281027201999*$A1**2 + 0.561322848096649*$A1 + -1.7596818799488065)*$KG_TO_POUND),
            strval((-4.6311811077683536e-07*$A1**4 + 0.00013257956134424934*$A1**3 + -0.01390542985656028*$A1**2 + 0.6467578605933515*$A1 + -1.8598596375229453)*$KG_TO_POUND),
            strval((-5.384111201084763e-07*$A1**4 + 0.00015347358908468338*$A1**3 + -0.016000074453347543*$A1**2 + 0.7363305755883174*$A1 + -1.9353474385271872)*$KG_TO_POUND),
        );

        return $male_centile;

    }
    if ($weight_group == 'iii'){
        $male_centile  = array(

            strval((-1.3333560657935285e-07*$A1**4 + 3.996061063296184e-05*$A1**3 + -0.0044098102079643976*$A1**2 + 0.22454973146497828*$A1 + -0.9685122711078415)*$KG_TO_POUND),
            strval((-1.8170265159115086e-07*$A1**4 + 5.5534366348262985e-05*$A1**3 + -0.006302042249135272*$A1**2 + 0.33344263189299833*$A1 + -1.5745645832147754)*$KG_TO_POUND),
            strval((-2.457372120974676e-07*$A1**4 + 7.534398411697677e-05*$A1**3 + -0.0085839265186452*$A1**2 + 0.4531597873922351*$A1 + -2.163880925236395)*$KG_TO_POUND),
            strval((-3.2126806171754075e-07*$A1**4 + 9.793967610235032e-05*$A1**3 + -0.011080201663233613*$A1**2 + 0.5759152321904724*$A1 + -2.7187771831976946)*$KG_TO_POUND),
            strval((-4.0205074667588654e-07*$A1**4 + 0.00012170901893848603*$A1**3 + -0.013650525430325472*$A1**2 + 0.6981527498091528*$A1 + -3.217681917050801)*$KG_TO_POUND),
            strval((-4.88151001498871e-07*$A1**4 + 0.0001468068055678368*$A1**3 + -0.016315499487949105*$A1**2 + 0.8197623957872828*$A1 + -3.6268530325292248)*$KG_TO_POUND),
            strval((-5.899481275345117e-07*$A1**4 + 0.00017545609838770808*$A1**3 + -0.01922568274037046*$A1**2 + 0.9432497518086964*$A1 + -3.9192133687046016)*$KG_TO_POUND),
            strval((-6.96864214171319e-07*$A1**4 + 0.00020526775641574804*$A1**3 + -0.02219439583331513*$A1**2 + 1.0632897646525337*$A1 + -4.0381707251280945)*$KG_TO_POUND),
            strval((-8.035358006988042e-07*$A1**4 + 0.0002344870185084129*$A1**3 + -0.0250285183201403*$A1**2 + 1.1722336099794382*$A1 + -3.979986462940348)*$KG_TO_POUND),
        );

        return $male_centile;

    }
    if ($weight_group == 'iv'){
        $male_centile  = array(
            strval((-4.20442072305298e-07*$A1**4 + 0.00014068862035788128*$A1**3 + -0.017320660056635632*$A1**2 + 0.9586843045038461*$A1 + -6.9040352635786695)*$KG_TO_POUND),
            strval((-5.739535031272406e-07*$A1**4 + 0.00018929531803320868*$A1**3 + -0.023042466576621526*$A1**2 + 1.2501065292220896*$A1 + -8.512418560181132)*$KG_TO_POUND),
            strval((-7.208728554153729e-07*$A1**4 + 0.0002343424155426012*$A1**3 + -0.028173557404259374*$A1**2 + 1.5043861535884124*$A1 + -9.682292314957849)*$KG_TO_POUND),
            strval((-8.356953822899716e-07*$A1**4 + 0.0002698469047751194*$A1**3 + -0.03225510373781577*$A1**2 + 1.709925401823281*$A1 + -10.429681331386648)*$KG_TO_POUND),
            strval((-9.319828276908646e-07*$A1**4 + 0.00029963942129203287*$A1**3 + -0.03569460937525267*$A1**2 + 1.8862288675713328*$A1 + -10.970470479961108)*$KG_TO_POUND),
            strval((-1.0289834410693982e-06*$A1**4 + 0.000328995199628575*$A1**3 + -0.03899325251094193*$A1**2 + 2.0496267785034386*$A1 + -11.363198021410566)*$KG_TO_POUND),
            strval((-1.1285940097242359e-06*$A1**4 + 0.0003582383024030115*$A1**3 + -0.042157884494738576*$A1**2 + 2.1971124625069924*$A1 + -11.445605578744479)*$KG_TO_POUND),
            strval((-1.2237050860919385e-06*$A1**4 + 0.0003860157432496913*$A1**3 + -0.04511868987158999*$A1**2 + 2.329464779947437*$A1 + -11.249536009730427)*$KG_TO_POUND),
            strval((-1.3016193740693984e-06*$A1**4 + 0.0004087003949901311*$A1**3 + -0.047518557201575606*$A1**2 + 2.433642329259087*$A1 + -10.7454491765812)*$KG_TO_POUND),
        );

        return $male_centile;

    }
    if ($weight_group == 'v'){
        $male_centile  = array(

            strval((-1.5557935017829426e-07*$A1**4 + 9.323720718052506e-05*$A1**3 + -0.016150952561108912*$A1**2 + 1.154126992982778*$A1 + -8.49601486220326)*$KG_TO_POUND),
            strval((-4.258073619179502e-07*$A1**4 + 0.00017326279755831397*$A1**3 + -0.02465392271146272*$A1**2 + 1.5251877494067256*$A1 + -10.670884969133457)*$KG_TO_POUND),
            strval((-6.583349826903699e-07*$A1**4 + 0.0002429865019592522*$A1**3 + -0.03214267976194538*$A1**2 + 1.8549131745018437*$A1 + -12.301453532119268)*$KG_TO_POUND),
            strval((-8.663896662551962e-07*$A1**4 + 0.00030440894605356707*$A1**3 + -0.038659683178475825*$A1**2 + 2.1386307382677527*$A1 + -13.439908929048611)*$KG_TO_POUND),
            strval((-1.0579642072708652e-06*$A1**4 + 0.00036044247068738007*$A1**3 + -0.04453778550549095*$A1**2 + 2.390151057419991*$A1 + -14.232783125551137)*$KG_TO_POUND),
            strval((-1.2248169558837068e-06*$A1**4 + 0.0004095645979855578*$A1**3 + -0.04973732843384803*$A1**2 + 2.6156744133301344*$A1 + -14.752796157467682)*$KG_TO_POUND),
            strval((-1.375374685489713e-06*$A1**4 + 0.0004545498525313166*$A1**3 + -0.05458492614698155*$A1**2 + 2.8333661435256845*$A1 + -15.206879400704755)*$KG_TO_POUND),
            strval((-1.5000205137532569e-06*$A1**4 + 0.000492972349530313*$A1**3 + -0.05887158008266778*$A1**2 + 3.0360109406533073*$A1 + -15.50267829406749)*$KG_TO_POUND),
            strval((-1.6015893607871644e-06*$A1**4 + 0.0005249224451835395*$A1**3 + -0.06251924507696079*$A1**2 + 3.2156105082083513*$A1 + -15.65494721271572)*$KG_TO_POUND),
        );

        return $male_centile;

    }



    $male_centile  = array(    

        strval((-4.15194E-07*$A1**4 + 1.38890E-04*$A1**3 - 1.71218E-02*$A1**2 + 9.49697E-01*$A1 - 6.62088E+00)*$KG_TO_POUND),
        strval((-5.37115E-07*$A1**4 + 1.77582E-04*$A1**3 - 2.17375E-02*$A1**2 + 1.18943E+00*$A1 - 7.43317E+00)*$KG_TO_POUND),
        strval((-4.96516E-07*$A1**4 + 1.78531E-04*$A1**3 - 2.33543E-02*$A1**2 + 1.33420E+00*$A1 - 7.53900E+00)*$KG_TO_POUND),
        strval((-9.93129E-07*$A1**4 + 3.05935E-04*$A1**3 - 3.49003E-02*$A1**2 + 1.76694E+00*$A1 - 1.00132E+01)*$KG_TO_POUND),
        strval((-8.48280E-07*$A1**4 + 2.77553E-04*$A1**3 - 3.38133E-02*$A1**2 + 1.83621E+00*$A1 - 1.10587E+01)*$KG_TO_POUND),
        strval((-7.89882E-07*$A1**4 + 2.67672E-04*$A1**3 - 3.34359E-02*$A1**2 + 1.83778E+00*$A1 - 8.38638E+00)*$KG_TO_POUND),
        strval((-1.36463E-06*$A1**4 + 4.08517E-04*$A1**3 - 4.55508E-02*$A1**2 + 2.26579E+00*$A1 - 1.12878E+01)*$KG_TO_POUND),
        strval((-1.23337E-06*$A1**4+ 3.82124E-04*$A1**3 - 4.38113E-02*$A1**2 + 2.21885E+00*$A1 - 8.37327E+00)*$KG_TO_POUND),
        strval((-1.41623E-06*$A1**4 + 4.38357E-04*$A1**3 - 4.97970E-02*$A1**2 + 2.48275E+00*$A1 - 1.04020E+01)*$KG_TO_POUND),
       

    );

    return $male_centile;
}

function get_lower_bound_centiles($pure_dob,$weights,$dates,$current_dog_gender,$weight_group){  //for user generate weights
    $dob_dt = new DateTime($pure_dob); //dt = datetime object
    foreach($dates as $key=>$date){
        $weight = $weights[$key];
        $date_dt = new DateTime($date);
        $age = $dob_dt->diff($date_dt);  #latest age based relative to the last time the dog was weighed.
        $age_in_weeks = ($age->days)/7.0;

        if ($current_dog_gender == '1'){
            $centiles =  generate_female_centile($age_in_weeks,$weight_group);
        }
        if ($current_dog_gender == '2'){
            $centiles =  generate_male_centile($age_in_weeks,$weight_group);
        }

        $lower_bound_centile_id = -1; //flag of minus 1 shows that dog is severly underweight for the weight category
        //lower_bound_centile_id == len(centiles)-1 means severly overweights 
        foreach($centiles as $key=>$standard_weight) {
            if ((float)$weight > (float)$standard_weight){
                $lower_bound_centile_id = $key;

            }

        }
        $lower_bound_centiles_ids[] = $lower_bound_centile_id; #lower bound centile ids for user-recorded weight

    }
    return $lower_bound_centiles_ids;
}




function get_bounding_centiles_info($pure_dob,$last_weight,$last_weighting_date,$current_dog_gender,$weight_group){ 

        //echo $last_weighting_date.'<br>';
        //echo $pure_dob.'<br>';
        //echo $last_weight.'<br>';
        $dob_dt = new DateTime($pure_dob); //dt = datetime object
        $last_date_dt = new DateTime($last_weighting_date);
        $dog_latest_age = $dob_dt->diff($last_date_dt);  #latest age based relative to the last time the dog was weighed.
        $dog_latest_age_in_weeks = ($dog_latest_age->days)/7.0;

        if ($current_dog_gender == '1'){
            $centiles =  generate_female_centile($dog_latest_age_in_weeks,$weight_group);
        }
        if ($current_dog_gender == '2'){
            $centiles =  generate_male_centile($dog_latest_age_in_weeks,$weight_group);
        }


        //echo $dog_latest_age_in_weeks.'<br>';

        $lower_bound_centile_id = -1; // -1 is a flag that show the current dog weight is lower that the lowest weight in centiles of category
        foreach($centiles as $key=>$standard_weight) {
            if ((float)$last_weight > (float)$standard_weight){
                $lower_bound_centile_id = $key;

            }
        }

        if ($lower_bound_centile_id == -1){
            //echo 'lower centile id -1 <br>';
            return array();
        }


        $upper_bound_centile_id =  $lower_bound_centile_id + 1;

        if ($upper_bound_centile_id == 9){
            //echo 'upper centile id 9 <br>';
            return array();
        }

        print_r($centiles);
        print_r('<br>');
        print_r($lower_bound_centile_id);
        print_r('<br>');
        print_r($upper_bound_centile_id);

        #edge_tendency_ratio is the fraction of how far the weight is from the centile boundaries for that age, 0 means closer to lower bound


        $edge_tendency_ratio = ($last_weight - $centiles[$lower_bound_centile_id])/($centiles[$upper_bound_centile_id] - $centiles[$lower_bound_centile_id]);

        //print_r('<br>');
        //print_r($edge_tendency_ratio);
        //print_r('<br>');

        return array($lower_bound_centile_id,$upper_bound_centile_id,$edge_tendency_ratio,$dog_latest_age_in_weeks);

}


$standard_curves = array();  //standard growth curve (weight values)
        $standard_dates = array();   // corresponding dates for the growth curve weight, baseline of date will be current dog dob

        
    
        $standard_ages_in_weeks = range(12,100,2);//array(12,24,48);  // time intervals for growth curve

        $KG_TO_POUND = 2.20462;
        $POUND_TO_KG = 1/$KG_TO_POUND;
        $weight_conv = $KG_TO_POUND;
        //if ($breed_weight_group == 'iv'){
        if (true){
            global $standard_centiles;
            //
            foreach ($standard_ages_in_weeks as $A1) {
                
                
                $a_standard_age_in_days = $A1*7;  

                $a_standard_age_in_days = 'P'.$a_standard_age_in_days.'D'; //argument for date adds
                
                
                $current_dog_dob = $pure_dob; //+++
                $current_dog_dob=new DateTime($current_dog_dob);//+++
                $a_standard_date = clone $current_dog_dob; // clone becasue the add the comes next will mutate

                $a_standard_date->add(new DateInterval($a_standard_age_in_days));

                $a_standard_date = $a_standard_date->format('Y-m-d'); 

                $standard_dates[] = $a_standard_date;    

                $female_centile = generate_female_centile($A1,$weight_group);
                
                $male_centile = generate_male_centile($A1,$weight_group);

                if ($current_dog_gender == '1'){
                    
                    $standard_centiles[] = $female_centile;
                   
                }
                if ($current_dog_gender == '2'){
                    
                    $standard_centiles[] = $male_centile;
                }
            }

            ////COPY FROM HERE TO REAL CODE
            
            global $last_weighting_date;
            $last_weighting_date = end($dates);
            global $last_weight;
            $last_weight = end($weights);
            
            $bounding_centiles_info = get_bounding_centiles_info($pure_dob,$last_weight,$last_weighting_date,$current_dog_gender,$weight_group);
            if ($bounding_centiles_info == array()){
                $projected_weights = array();
            }
            else{
                $lower_bound_centile_id = $bounding_centiles_info[0];
                $upper_bound_centile_id = $bounding_centiles_info[1];
                $edge_tendency_ratio = $bounding_centiles_info[2];
                $dog_latest_age_in_weeks = $bounding_centiles_info[3];

                $projected_ages_in_weeks = range($dog_latest_age_in_weeks,100,2);

                $projected_weights = array();

                $projected_dates = array();

                foreach ($projected_ages_in_weeks as $A1) {
                    
                    
                    $a_projected_age_in_days = $A1*7;  

                    $a_projected_age_in_days = 'P'.$a_projected_age_in_days.'D'; //argument for date adds
                    
                    $pure_dob = '2022-01-01'; //+++
                    $current_dog_dob = $pure_dob; //+++
                    $current_dog_dob=new DateTime($current_dog_dob);//+++
                    $a_projected_date = clone $current_dog_dob; // clone becasue the add the comes next will mutate

                    $a_projected_date->add(new DateInterval($a_projected_age_in_days));

                    $a_projected_date = $a_projected_date->format('Y-m-d'); 

                    $projected_dates[] = $a_projected_date;    

                    
                    
                    

                    if ($current_dog_gender == '1'){
                        $female_centile = generate_female_centile($A1,$weight_group);

                        $female_centile_lower = $female_centile[$lower_bound_centile_id];
                        $female_centile_upper = $female_centile[$upper_bound_centile_id];

                        $a_projected_weight = $female_centile_lower + ($female_centile_upper - $female_centile_lower)*$edge_tendency_ratio;
                        
                        //echo  $female_centile_lower.'   '.$female_centile_upper.'  '.$a_projected_weight.'<br>';
                        
                        $projected_weights[] = strval($a_projected_weight);
                    
                    }
                    if ($current_dog_gender == '2'){

                        $male_centile = generate_male_centile($A1,$weight_group);

                        $male_centile_lower = $male_centile[$lower_bound_centile_id];
                        $male_centile_upper = $male_centile[$upper_bound_centile_id];

                        $a_projected_weight = $male_centile_lower + ($male_centile_upper - $male_centile_lower)*$edge_tendency_ratio;
                        
                        //echo  $male_centile_lower.'   '.$male_centile_upper.'  '.$a_projected_weight.'<br>';

                        $projected_weights[] = strval($a_projected_weight);
                    }
                } //for each projected weight
            } // else bounding info not null

            //centile crossing code from here
            $lower_bound_centiles = get_lower_bound_centiles($pure_dob,$weights,$dates,$current_dog_gender,$weight_group);

        } //for the if playground

        print_r ($projected_weights);

        
