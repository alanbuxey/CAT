<?php
function my_nonce($optSalt = '') {
    $remote = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    return hash_hmac('sha256', session_id() . $optSalt, date("YmdG") . '1qaz2wsx3edc!QAZ@WSX#EDC' . $remote);
}
error_reporting(E_ALL | E_STRICT);
$Gui->defaultPagePrelude();
$_SESSION['current_page'] = $_SERVER['SCRIPT_NAME'];
?>
<!-- JQuery -->
<script type="text/javascript" src="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/jquery.js"); ?>"></script>
<script type="text/javascript" src="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/jquery-ui.js"); ?>"></script>
<script type='text/javascript' src="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/Timepicker/jquery-ui-timepicker-addon.js"); ?>"></script>
<script type='text/javascript' src="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/Timepicker/jquery-ui-sliderAccess.js"); ?>"></script>
<link type="text/css"  rel="stylesheet" href="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/jquery-ui-1.12.1.custom/jquery-ui.css"); ?>" media="all" />
<link type="text/css"  rel="stylesheet" href="<?php echo $Gui->skinObject->findResourceUrl("EXTERNAL", "jquery/Timepicker/jquery-ui-timepicker-addon.css"); ?>"  media="all" />
<script type="text/javascript">
    var recognisedOS = '';
    var downloadMessage;
    var noDisco = 1;
    var sbPage = 1;
    var lang = "<?php echo($Gui->langObject->getLang()) ?>";
    var dir = "<?php echo dirname(__DIR__); ?>";

<?php
$admin = filter_input(INPUT_GET, 'admin', FILTER_SANITIZE_NUMBER_INT);
$profile_list_size = 1;
include_once(dirname(__DIR__) . '/Divs.php');
$divs = new Divs($Gui);
$operatingSystem = $Gui->detectOS();
$Gui->loggerInstance->debug(4, $operatingSystem);
$uiElements = new web\lib\admin\UIElements();
if ($operatingSystem) {
    print "recognisedOS = '" . $operatingSystem['device'] . "';\n";
}
include(dirname(__DIR__) . '/user/js/cat_js.php');
?>

</script>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $Gui->skinObject->findResourceUrl("CSS", "cat-user.css"); ?>" />

</head>
<body>
<div id="main_page">
    <div id="loading_ico">
          <span id='load_comment'></span><br><img src="<?php echo $Gui->skinObject->findResourceUrl("IMAGES", "icons/loading51.gif"); ?>" alt="Loading stuff ..."/>
    </div>
    <form id="cat_form" name="cat_form" method="POST"  accept-charset="UTF-8" action="">
    <input name="myNonce" id="myNonce" type="hidden" value="<?php echo my_nonce($_SERVER['SCRIPT_NAME']); ?>">
    <?php echo $divs->div_heading(); ?>
    <div id="info_overlay"> <!-- device info -->
        <div id="info_window"></div>
        <img id="info_menu_close" class="close_button" src="<?php echo $Gui->skinObject->findResourceUrl("IMAGES", "icons/button_cancel.png"); ?>" ALT="Close"/>
    </div>
    <div id="main_menu_info" style="display:none"> <!-- stuff triggered form main menu -->
          <img id="main_menu_close" class="close_button" src="<?php echo $Gui->skinObject->findResourceUrl("IMAGES", "icons/button_cancel.png"); ?>" ALT="Close"/>
          <div id="main_menu_content"></div>
    </div>
    <div id="main_body">
        <div id="user_page">
            <?php echo $divs->div_pagetitle(_("Diagnostics site"), _("We will do our best to resolve your problems!<br>Help us and answer precisely to our questions.")); ?>
            <div id="user_info" style='padding-top: 10px;'>
            <div id='diagnostic_choice'>
                <?php echo _("Are you reporting the problem as"); ?>
                <input type='radio' name='diagnostic_usertype' value='0'><?php echo _("an end-user") . ' ' . _("or"); ?>   
                <input type='radio' name='diagnostic_usertype' value='1' <?php if ($admin == 1) { echo " checked"; } ?> > <?php echo _("an eduroam administrator"); ?>
            </div>
            <div id='diagnostic_enduser' style='display: none;'>
                <h2><?php echo '<h2>' . _("Tools for End Users"); ?></h2>
                <p>
                <?php 
                    echo _("To resolve your problem a real-time diagnostics for your realm must be performed.");
                ?>
                </p>
                <?php
                    echo '<h3>' . _("We need some information on your home institiution - issuer of your account") . '</h3>';
                    echo _("State your realm:");
                ?>
                <input type='text' name='user_realm' id='user_realm' value=''>
                <?php
                    echo '<br/>' . _("or") . '<br/>';
                    echo _("we will try to guess your realm:") . '<br/>';
                    echo '<div id="select_idp_country"><a href="" id="idp_countries_list">';    
                    echo '<span id="realmselect">' . _("click to select your country and organisation") . '</a></span></div>';
                ?>
                <div id="select_idp_area" style="display:none;">
                </div>
                <div>
                    <?php
                        echo '<h3>' . _("Optionally, to improve tests, you can provide information on your current location") . '</h3>';
                        echo '<div id="select_sp_country"><a href="" id="sp_countries_list">';    
                        echo '<span id="spselect">' . _("click to select a location in which you have an eduroam problem") . '</a></span></div>';
                    ?>
                    <div id="select_sp_area" style="display:none;">
                    </div>
                </div>
                <div id="sociopath_query_area" style="margin-top:20px; display:none;">
                    <b>
                        <?php echo _("Now we have a few questions..."); ?>
                    </b>
                    <div id="sociopath_queries"></div>
                </div>
                <div id="start_test_area" style="padding-top: 10px; display:none; text-align:center;">
                    <button id='realmtest' accesskey="T" type='button'><?php echo _("Go!"); ?>
                    </button>
                </div>
            </div>
            <div id='diagnostic_admin' style='display: <?php if (!$admin) { echo 'none'; } ?> ;'>
                <h2><?php echo _("Tools for eduroam admins"); ?></h2>
                <?php
                    require_once(CONFIG['AUTHENTICATION']['ssp-path-to-autoloader']);
                    $auth = new \web\lib\admin\Authentication();
                    echo '<input type="hidden" id="isadmin" value="';
                    if ($auth->isAuthenticated()) {
                        echo "1\">";
                        echo "<div id='admin_test_area' style='display: ";
                        if (!$admin) {
                            echo 'none';
                        }
                        echo ";'>";
                        echo '<h3>' . _("Which problem are you reporting?") . '</h3>';
                        echo '<input type="radio" name="problem_type" value="1">';
                        echo _("SP contacting IdP due to technical problems or abuse") . '<br>';
                        echo '<input type="radio" name="problem_type" value="2">';
                        echo _("IdP contacting SP due to technical problems");
                        echo "<div id='idp_contact_area'></div>";
                        echo "<div id='sp_abuse'></div>";
                        echo "<div id='idp_problem'></div>";
                        echo "</div>"; 
                    } else {
                        echo "0\">";
                        echo _("This service is for authenticated admins only.") . '<br>';
                        echo "<a href=\"diag.php?admin=1\">" .
                            _("eduroam® admin access is needed") . "</a>";
                    }
                ?>
            </div> 
            </div>
                <input type="hidden" name="lang" id="lang"/>
        </div>
    </div>
   </form>
</div>
<?php echo $divs->div_footer(); 
    
?>
<script>
    function countryAddSelect(select, options, type) {
        select = select + options + '</select></td>';
        $('#select_'+type+'_country').hide();
        var shtml = '<table><tbody><tr id="row_'+type+'_country"></tr>';
        shtml = shtml + '<tr id="row_'+type+'_institution" style="visibility: collapse;">';
        shtml = shtml + '<td>' + <?php echo '"' . _("Select institiution") . '"'; ?> + '</td><td></td></tr>';
        if (type === 'idp') {
            shtml = shtml + '<tr id="row_idp_realm"></tr>';
        }
        shtml = shtml + '</tbody></table>';
        $('#select_'+type+'_area').html(shtml);
        $('#select_'+type+'_area').show();
        $('#row_'+type+'_country').append(select);
    }
    function countrySelection(type1) {
        var type2;
        if (type1 === 'sp') {
            type2 = 'idp';
        }
        if (type1 === 'idp') {
            type2 = 'sp';
        }
        var options = '';
        var select = <?php echo '"<td>' . _("Select country:") . ' </td>"'; ?>;
        select = select + '<td><select id="' + type1 + '_country" name="' + type1 + '_country" style="width:400px;">';
        if ($("#"+type2+"_country").is('select')) {
            options = ($('#'+type2+'_country').html());
            countryAddSelect(select, options, type1);
        } else {
            var comment = <?php echo '"' . _("Fetching countries list") . '..."'; ?>;
            inProgress(1, comment);
            $.ajax({
                url: "findRealm.php",
                data: {type: "co", lang: lang},
                dataType: "json",
                success:function(data) {
                    if (data.status) {
                        inProgress(0);
                        options = '<option value=""></option>';
                        var countries = data.countries;
                        for (var key in countries) {
                            options  = options + '<option value="'+key+'">' + countries[key] + '</option>';
                        }
                        countryAddSelect(select, options, type1);
                    }
                },
                error:function() {
                    passed = false;
                    inProgress(0);
                    alert('error');
                }
            });
        }  
    }
    function isDomain(realm) {
        var re = new RegExp(/^(([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]|[a-zA-Z0-9])\.)*[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/);
        if (re.test(realm)) {
            return true;
        }
        return false;
    }
    function testSociopath(realm, answer) {
        var comment = <?php echo '"' . _("Testing realm") . '..."'; ?>; 
        inProgress(1, comment);
        if ($('#tested_realm').length == 0) {
            $('<input>').attr({
                type: 'hidden',
                id: 'tested_realm',
                value: realm
            }).appendTo('form');
        }  
        console.log('realm - '+realm+', answer='+answer);
        $.ajax({
            url: "processSociopath.php",
            data: {answer: answer},
            dataType: "json",
            success:function(data) {
                $('#start_test_area').hide();
                if (data) {
                    inProgress(0);
                    console.log(data);
                    if (data['NEXTEXISTS']) {
                        if ($('#sociopath_queries').html() == '') {
                            var query = '';
                            if ($('#tested_realm').length == 0) {
                                query = '<input type="hidden" id="tested_realm" value="' + realm + '">';
                            }    
                            query = query + '<div id="current_query">'+data['TEXT']+'</div>';
                            query = query + '<div><button id="answer_yes">' + <?php echo '"' . _("Yes") . '"'; ?> + '</button>';
                            query = query + '<button style="margin-left:20px;" id="answer_no">' + <?php echo '"' . _("No") . '"'; ?> + '</button>';
                            query = query + '<button style="margin-left:20px;" id="answer_noidea">' + <?php echo '"' . _("I don't know") . '"'; ?> + '</button></div>';
                            $('#sociopath_queries').html(query);
                            $('#sociopath_query_area').show();
                        }
                        else {
                            $('#current_query').html(data['TEXT']);
                        }
                   } else {
                        console.log('lądujemy...');
                        var realm = $('#tested_realm').val();
                        $('#tested_realm').remove();
                        $('#sociopath_query_area').hide();
                        $('#sociopath_queries').html('');
                        $('#start_test_area').show();
                        finalVerdict(realm, data['SUSPECTS'])
                   }
                }
            },
            error:function() {
                inProgress(0);
                alert('error');
            }
       }); 
    }
    function finalVerdict(realm, verdict) {
        var title = <?php echo '"' . _("Diagnistic tests results for selected realm") . '"'; ?>;
        result = '<div class="padding">';
        result = result + '<div><h3>';
        result = result + <?php echo '"' . _("The result for tested realm:") . ' "'; ?> + realm;
        result = result + '</h3></p><div style="padding: 5px;"><div style="padding: 0px;">';
        result = result + <?php echo '"' . _("We located") . '" '; ?>  + ' ';
        result = result + Object.keys(verdict).length + ' ';
        result = result + <?php echo '"' . _("suspected areas which potentially can cause a problem.") . '"'; ?> + '<br>';
        result = result + <?php echo '"' . _("Next to the problem description we show a speculated probability of this event.") . '"'; ?>;
        result = result + '</div><div style="padding: 5px;"><table>';
        k = 1;
        for (key in verdict) {
            result = result + '<tr><td>' + k + '.</td>';
            k = k + 1;
            if (key === 'INFRA_DEVICE') {
                result = result + '<td>' + <?php echo '"' . _("Your device configuration is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_SP_80211') {
                result = result + '<td>' + <?php echo '"' . _("WIFI network around you sucks") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_SP_LAN') {
                result = result + '<td>' + <?php echo '"' . _("The network environment around you is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_SP_RADIUS') {
                result = result + '<td>' + <?php echo '"' . _("RADIUS server of your service provider has a problem") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_IDP_AUTHBACKEND') {
                result = result + '<td>' + <?php echo '"' . _("RADIUS server in your home institution has a problem to authenticate users") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_NRO_SP') {
                result = result + '<td>' + <?php echo '"' . _("The link between your current location and your federation server is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_LINK_ETLR_NRO_SP') {
                result = result + '<td>' + <?php echo '"' . _("The link between your current location, your federation server and top level server is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_LINK_ETLR_NRO_IdP') {
                result = result + '<td>' + <?php echo '"' . _("The link between your home institution, your federation server and top level server is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_ETLR') {
                result = result + '<td>' + <?php echo '"' . _("The communication to top level server is down") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_ETLR_NRO_IdP') {
                result = result + '<td>' + <?php echo '"' . _("The link between top level server and your federation server is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_ETLR_NRO_SP') {
                result = result + '<td>' + <?php echo '"' . _("The link between top level server and your service provider is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_NRO_IdP') {
                result = result + '<td>' + <?php echo '"' . _("The link between your federation server and your institution server is broken") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_IdP_RADIUS') {
                result = result + '<td>' + <?php echo '"' . _("RADIUS server of your home institution has a problem") . '"'; ?> + '</td>';
            }
            if (key === 'INFRA_NONEXISTENTREALM') {
                result = result + '<td>' + <?php echo '"' . _("Entered realm doesn't exist") . '"'; ?> + '</td>';
            }
            result = result + '<td style="padding-left: 5px;">' + (verdict[key] * 100).toFixed(2) + "%</td></tr>";
        }
        result = result + '</table></div></div>';
        result = result + '</div>';
        result = result + '</div></div>';
        showInfo(result, title);
    }
        function formatMAC(e) {
        var r = /([a-f0-9]{2})([a-f0-9]{2})/i,
        str = e.target.value.replace(/[^a-f0-9]/ig, "");
        while (r.test(str)) {
            str = str.replace(r, '$1' + ':' + '$2');
        }
        e.target.value = str.slice(0, 17);
    };
    function isEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    };
    function inProgress(s, comment) {
        var b = true;
        if (s === 1) {
            var x = getWindowHCenter() - 16;
            var h = ($("body").height() - 128)/2;
            $("#loading_ico").css('left',x+'px');
            $("#loading_ico").css('top',h+'px');
            $("#loading_ico").show();
            if (typeof comment !== 'undefined') {
                $("#load_comment").html(comment);
            } else {
                $("#load_comment").html("");
            }
        } else {
            $("#loading_ico").hide();
            b = false;
            $("#load_comment").html("");
        }
        var catForm = document.forms['cat_form'];
        if (!catForm) {
            catForm = document.cat_form;
        }
        var elements = catForm.elements;
        for (var i = 0, len = elements.length; i < len; ++i) {
            elements[i].disabled = b;
        }
    }
    
    $('input[name="diagnostic_usertype"]').click(function() {   
        var t = $('input[name=diagnostic_usertype]:checked').val();
        if (t > 0) {
            $('#diagnostic_enduser').hide();
            $('input[name=problem_type]').each(function() {
                $(this).prop('checked', false);
            });
            $('#user_realm').val('');
            $('#select_idp_area').html('');
            $('#select_idp_country').show();
            $('#select_sp_area').html('');
            $('#select_sp_area').html('');
            $('#sociopath_queries').show();
            $('#diagnostic_admin').show();
            if ($('#isadmin').val() === "1") {
                $('#admin_test_area').show();
            }
        } else {
            $('#diagnostic_admin').hide();
            $('#idp_contact_area').html('');
            $('#sp_abuse').html('');
            $('#idp_problem').html('');
            $('#diagnostic_enduser').show();
        }
       
    });
    $('#user_realm').bind('autocompleteSelect', function(event, node) {
        console.log(node);
    });
    $('#user_realm').bind('change keyup blur input', function(e)  {
        console.log($('#user_realm').val());
        if (isDomain($('#user_realm').val())) {
            $('#start_test_area').show();
        } else {
            $('#start_test_area').hide();
            $('#select_idp_area').hide();
            $('#select_idp_area').html('');
            $('#select_idp_country').show();
        }
    });
    $('#idp_countries_list').click(function(event){
        event.preventDefault();
        $('#start_test_area').hide();
        $('#user_realm').val("");
        countrySelection("idp");
        return false;
    });
    $('#sp_countries_list').click(function(event){
        /*$('#countries_list').removeAttr("href");*/
        event.preventDefault();
        countrySelection("sp");
        return false;
    });
    $(document).on('change', '#idp_country, #sp_country' , function() {
        var comment = <?php echo '"' . _("Fetching institutions list") . '..."'; ?>;  
        var id = $(this).attr('id');
        var k = id.indexOf('_');
        var type = id.substr(0,k);
        co=$('#'+type+'_country').val();
        if (co !== "") {
            inProgress(1, comment);
            $.ajax({
                url: "findRealm.php",
                data: {type: 'inst', co: co, lang: lang},
                dataType: "json",
                success:function(data) {
                    if (data.status === 1) {
                        inProgress(0);
                        var institutions = data.institutions;
                        var select = <?php echo '"<td>' . _("Select institution:") . '</td>"'; ?>;
                        select = select + '<td><select id="' + type + '_inst" name="' + type + '_inst" style="width:400px;"><option value=""></option>';
                        for (var i in institutions) {
                            select = select + '<option value="' + institutions[i].ID + '">' + institutions[i].name + '</option>';
                        }
                        select = select + '</select></td>';
                        $('#row_' + type + '_institution').html('');
                        $('#row_' +type + '_institution').append(select);
                        $('#row_' +type + '_realm').html('');
                        $('#row_' +type + '_institution').css('visibility', 'visible');
                    }
                },
                error:function() {
                    inProgress(0);
                    alert('error');
                }
            }); 
        } else {
            $('#' + type + '_inst').remove();
            $('#row_' + type + '_institution').css('visibility', 'collapse');
            $('#start_test_area').hide();
            $('#row_idp_realm').html("");
        }
        return false;
    });
    $(document).on('change', '#idp_inst' , function() {
        inst=$("#idp_inst").val();
        if (inst === '') {
            $('#row_idp_realm').html("");
            $('#start_test_area').hide();
            return false;
        }
        var comment = <?php echo '"' . _("Fetching realms list") . '..."'; ?>;
        inProgress(1, comment);
        $.ajax({
            url: "findRealm.php",
            data: {type: 'realm', ou: inst, lang: lang},
            dataType: "json",
            success:function(data) {
                inProgress(0);
                if (data.status === 1) {
                    var realms = data.realms;
                    var realmselect = '';
                    if (realms.length > 1) {
                        realmselect = <?php echo '"<td>' . _("Check realm(s):") . '</td>"'; ?>;
                        realmselect = realmselect + '<td>' + "<span style='margin-left: 19px'>";
                        for (var i in realms) {
                            console.log("i = "+i);
                            realmselect = realmselect + '<input type="radio" name="realm" ';
                            realmselect = realmselect + 'value="' + realms[i] + '"';
                            if (i === "0") {
                                realmselect = realmselect + ' checked';
                            }
                            realmselect = realmselect + '><label>' + realms[i] + '</label>';
                        }
                        realmselect = realmselect + '</span></td>';
                    } else {
                        realmselect = <?php echo '"<td>' . _("Realm:") . '</td>"'; ?>;
                        realmselect = realmselect + '<td>' + "<span style='margin-left: 19px'>";
                        realmselect = realmselect + realms[0] + '</span>';
                        realmselect = realmselect + '<input type="hidden" name="realm" value="' + realms[0] + '">';
                        realmselect = realmselect + '</span></td>';
                    }
                    $('#row_idp_realm').html("");
                    $('#row_idp_realm').append(realmselect);
                    $('#start_test_area').show();
                    $("#user_realm").val("");
                    $("#realm_info+ok").hide();
                }
            },
            error:function() {
                inProgress(0);
                alert('error');
            }
        }); 
        return false; 
    });
    $(document).on('click', '#realm_in_db, #realm_in_db_admin' , function() {
        var id = $(this).attr('id');
        var t = 0;
        if (id === 'realm_in_db') {
            $('#select_idp_area').hide();
            $('#select_idp_area').html('');
            $('#select_idp_country').show();
            realm = $("#user_realm").val();
        } else {
            realm = $("#admin_realm").val();
            $('#idp_contact_area').html('');
            $('#sp_questions > tbody  > tr').each(function() {
                if ($(this).attr('class') == 'visible_row') {
                    $(this).removeClass('visible_row').addClass('hidden_row');
                }
                if ($(this).attr('class') == 'error_row') {
                    $(this).remove();
                }
                $(this).children('td').each(function() {
                    $(this).children('input').each(function() {
                        if ($(this).prop('tagName').toLowerCase() === 'input' ||
                                $(this).prop('tagName').toLowerCase() === 'textarea') {
                            if ($(this).attr('id') !== 'admin_realm') {
                                $(this).val('');
                            }
                        }
                    });
                    $(this).children('textarea').each(function() {    
                        $(this).val('');
                    });
                });
            });
            t = 1;
        }
        var comment = <?php echo '"' . _("Running realm tests") . '..."'; ?>;
        inProgress(1, comment);
        /*waiting(comment);*/
        $.ajax({
            url: "findRealm.php",
            data: {realm: realm, lang: lang},
            dataType: "json",
            success:function(data) {
                inProgress(0);
                var realmFound = 0;
                if (data.status) {
                    var realms = data.realmlist.split(',');
                    for (var i = 0; i < realms.length; i++) {
                        if (realms[i] === realm) {
                            realmFound = 1;
                            break;
                        }
                    };
                }
                if (realmFound) { 
                    if (t == 0) {
                        $('#realm_info_ok').show();
                        $('#start_test_area').show();
                        $('#realm_info_fail').hide();
                    } else {
                        $('#sp_questions > tbody  > tr').each(function() {
                            if ($(this).attr('class') == 'hidden_row' && $(this).attr('id') != 'send_query_to_idp') {
                                $(this).removeClass('hidden_row').addClass('visible_row');
                            }
                        });
                        $('#idp_contact_area').append('<input type="hidden" name="idp_contact" id="idp_contact" value="' + data.admins + '">');
                    }
                } else {
                    if (t == 0) {
                        $('#realm_info_ok').hide();
                        $('#start_test_area').hide();
                        $('#realm_info_fail').show();
                    } else {   
                        $('#sp_questions > tbody  > tr').each(function() {
                            if ($(this).attr('class') == 'visible_row') {
                                $(this).removeClass('visible_row').addClass('hidden_row');
                            }
                        });
                        $('#sp_questions > tbody').append('<tr class="error_row"><td>' +
                                <?php echo '"' . _("Realm is not registered with the eduroam database:") . '"'; ?> +
                                '</td><td>' + realm + '</td></tr>');
                        $('#admin_realm').val('');
                    }    
                }
            },
            error: function (error) {
                alert('ERROR!');
            }
        });
        return false;
    });
    $(document).on('click', '#answer_yes, #answer_no, #answer_noidea' , function(e) {
        e.preventDefault();
        var answer = 1; /* No */
        if ($(this).attr('id') == 'answer_yes') {
            answer = 2; /* Yes */
        }
        if ($(this).attr('id') == 'answer_noidea') {
            console.log('I do not know');
            answer = 3; /* No idea */
        }
        testSociopath('', answer);
    });
    $('#realmtest').click(function(event){
        inProgress(1);
        var realm = '';
        if ($('#user_realm').val()) {
            realm = $('#user_realm').val();
        }
        if ($('#idp_inst').val()) {
            if ($('input[name="realm"]').attr('type') == 'hidden') {
                realm = $('input[name="realm"]').val();
            } else {
                $('input[name="realm"]').each(function() {
                    if ($(this).is(':checked')) {
                        realm = $(this).val();
                    } 
                });
            }
        }
        console.log('realm to test '+realm);
        var visited = 0;
        if ($('#sp_inst').val()) {
            visited = $('#sp_inst').val();
        }
        if (realm != '') {
            $.ajax({
                url: "magicTelepath.php",
                data: {realm: realm, lang: lang, visited: visited},
                dataType: "json",
                success:function(data) {
                    inProgress(0);
                    if (data.status === 1) {
                        var realm =  data.realm;
                        console.log('realm '+realm);
                        console.log(data.suspects);
                        testSociopath(realm, 0);
                    } else {
                        var title = <?php echo '"' . _("Diagnistic tests results for selected realms") . '"'; ?>;
                        result = '<div class="padding"><h3>' + <?php echo '"' . _("An unknown problem appears") . '"'; ?>;
                        result = result + '</h3>'
                        if (r.length == 1) {
                            result = result + <?php echo '"' . _("This test includes checking of following realm") . '"'; ?>;
                        } else {    
                            result = result + <?php echo '"' . _("This test includes checking of following realms") . '"'; ?>;
                        }
                        result = result + ': '
                        for (var i=0; i < r.length; i++) {
                            if (i > 0) {
                                result = result + ', ';
                            }
                            result = result + r[i];
                        }
                        result = result + '.<br>';
                        result = result + <?php echo '"' . _("You should report this to") . '"'; ?> + ' <a href="mailto:admin@eduroam.pl">admin@eduroam.pl</a>';
                        result = result + '</div>';
                        showInfo(result, title);
                    }
                    
                },
                error: function (error) {
                    inProgress(0);
                    alert('ERROR!');
                }
            });
        }
    });
    $(document).on('click', 'ul#ul-menu-list.tab-links a', function(e)  {
        e.preventDefault();
        /*var activeEl = $('.tab-links > li.active > a > active');*/
        var activeTab_a = $('.tab-links > li.active > a').attr('href');
        $('.tab-links > li.active').removeClass('active');
        var currentTab = $(this).attr('href');
        $(activeTab_a).removeClass('active');
        $(currentTab).addClass('active');
        $(this).parent().addClass('active');
    });
    $(document).on('click', '#realm_in_db_admin' , function() {
        var id = $(this).attr('id');
       
        realm = $("#admin_realm").val();
        $('#idp_contact_area').html('');
        $('#sp_questions > tbody  > tr').each(function() {
            if ($(this).attr('class') == 'visible_row') {
                $(this).removeClass('visible_row').addClass('hidden_row');
            }
            if ($(this).attr('class') == 'error_row') {
                $(this).remove();
            }
            $(this).children('td').each(function() {
                $(this).children('input').each(function() {
                    if ($(this).prop('tagName').toLowerCase() === 'input' ||
                            $(this).prop('tagName').toLowerCase() === 'textarea') {
                        if ($(this).attr('id') !== 'admin_realm') {
                            $(this).val('');
                        }
                    }
                });
                $(this).children('textarea').each(function() {    
                    $(this).val('');
                });
            });
        });
        var comment = <?php echo '"' . _("Running realm tests") . '..."'; ?>;
        inProgress(1, comment);
        $.ajax({
            url: "findRealm.php",
            data: {realm: realm, lang: lang},
            dataType: "json",
            success:function(data) {
                inProgress(0);
                var realmFound = 0;
                if (data.status) {
                    var realms = data.realmlist.split(',');
                    for (var i = 0; i < realms.length; i++) {
                        if (realms[i] === realm) {
                            realmFound = 1;
                            break;
                        }
                    };
                }
                if (realmFound) { 
                    $('#sp_questions > tbody  > tr').each(function() {
                        if ($(this).attr('class') == 'hidden_row' && $(this).attr('id') != 'send_query_to_idp') {
                            $(this).removeClass('hidden_row').addClass('visible_row');
                        }
                    });
                    $('#idp_contact_area').append('<input type="hidden" name="idp_contact" id="idp_contact" value="'+data.admins+'">');
                } else {
                    $('#sp_questions > tbody  > tr').each(function() {
                            if ($(this).attr('class') == 'visible_row') {
                                $(this).removeClass('visible_row').addClass('hidden_row');
                            }
                    });
                    $('#sp_questions > tbody').append('<tr class="error_row"><td>' + "Realm is not registered with the eduroam database:" +
                        '</td><td>' + realm + '</td></tr>');
                    $('#admin_realm').val('');
                }
            },
            error: function (error) {
                alert('ERROR!');
            }
        });
        return false;
    });
    $(document).on('click', '#submit_idp_query' , function() {
        realm = $('#admin_realm').val();
        email = $('#email').val();
        mac = $('#mac').val();
        why = $('#select_sp_problem').val();
        tst = $('#timestamp').val();
        txt = $('#freetext').val();
        idpc = $('#idp_contact').val();
        $.ajax({
            url: "adminQuery.php",
            data: {realm: realm, type: 'idp_send', reason: why, mac: mac, email: email, timestamp: tst, freetext: txt, idpcontact: idpc,lang: lang},
            dataType: "json",
            success:function(data) {
                if (data.status === 1) {
                    var result = '';
                    var title = <?php echo '"' . _("eduroam admin report submission") . '"'; ?>;
                    result = '<div class="padding">';
                    result = result + '<h3>'+ <?php echo '"' . _("SP contacting IdP due to technical problems or abuse") . '"'; ?> + '</h3>';
                    result = result + '<table>';
                    result = result + '<tr><td>' + <?php echo '"' . _("Reason") . '"'; ?> + '</td><td>' + data.reason + '</td></tr>';
                    result = result + '<tr><td>' + <?php echo '"' . _("SP email") . '"'; ?> + '</td><td>' + data.spcontact + '</td></tr>';
                    result = result + '<tr><td>' + <?php echo '"' . _("IdP email(s)") . '"'; ?> + '</td><td>' + data.idpcontact + '</td></tr>';
                    result = result + '<tr><td>' + <?php echo '"' . _("Event's timestamp") . '"'; ?> + '</td><td>' + data.timestamp + '</td></tr>';
                    result = result + '<tr><td>' + <?php echo '"' . _("Suspected MAC address") . '"'; ?> + '</td><td>' + data.mac + '</td></tr>';
                    result = result + '<tr><td>' + <?php echo '"' . _("Additional description") . '"'; ?> +'</td><td>' + data.description + '</td></tr>';
                    result = result + '</div>';
                    showInfo(result, title);
                }
            },
            error: function (error) {
                alert('ERROR!');
            }
        });
        return false;
    });
    $(document).on('blur', '#timestamp, #mac, #email' , function() {
         $(this).val($.trim($(this).val()));
         if ($('#mac').val().length > 0) {
            if ($('#mac').val().length != 17) {
                $('#mac').addClass('error_input');
                $('#mac').attr('title', <?php echo '"' . _("MAC address is incomplete") . '"'; ?>);
            } else {
                $('#mac').removeClass('error_input'); 
                $('#mac').attr('title', '');
            }
         } 
         if ($(this).attr('id') == 'email' &&  $(this).val().length > 0) {
            if (!isEmail($(this).val())) {
                $('#email').addClass('error_input');
                $('#email').attr('title', <?php echo '"' . _("Wrong format of email") . '"'; ?>);
            } else {
                $('#email').removeClass('error_input');
                $('#email').attr('title', '');
            }
         }
         if ($('#timestamp').val().length > 0  && $('#mac').val().length == 17 && $('#email').val().length > 0 && isEmail($('#email').val())) {
             $('#send_query_to_idp').removeClass('hidden_row').addClass('visible_row');
         } else {
             $('#send_query_to_idp').removeClass('visible_row').addClass('hidden_row');
         }
    });
    $('input[name="problem_type"]').click(function() {  
        var t = $('input[name=problem_type]:checked').val();
        if (t == 1) {
            if ($('#sp_abuse').html() === '') {
                $.get("adminQuery.php?type=sp", function(data, status) {
                    $('#sp_abuse').html(data);
                    $('#sp_abuse').show();
                    $('#idp_problem').hide();
                });
            }
            
        } else {
            $('#sp_abuse').html('');
            if ($('#idp_problem').html() === '') {
                $.get("adminQuery.php?type=idp", function(data, status) {
                    $('#idp_problem').html(data);
                    $('#sp_abuse').hide();
                    $('#idp_problem').show();
                });
            }
        }
    });
    $('#email').bind('keypress', function(e)  {
        if(e.keyCode == 13) {
            if ($('#timestamp').val().length > 0  && $('#mac').val().length == 17 && $('#email').val().length > 0 && isEmail($('#email').val())) {
                $('#send_query_to_idp').removeClass('hidden_row').addClass('visible_row');
            } else {
                $('#send_query_to_idp').removeClass('visible_row').addClass('hidden_row');
            }
            return false;
        }
    }); 
    $('#answer_yes, #answer_no').click(function(e) {
        e.preventDefault();
    });
    
</script>
<style>
.error_row td {
        color: red;
    }
    .hidden_row {
        visibility:collapse;
    }
    .visible_row {
        visibility:visible;
    }
    .error_input {
        border:1px solid red;
    }
</style>
</body>
