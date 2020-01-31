/*
 * *****************************************************************************
 * Contributions to this work were made on behalf of the GÉANT project, a 
 * project that has received funding from the European Union’s Framework 
 * Programme 7 under Grant Agreements No. 238875 (GN3) and No. 605243 (GN3plus),
 * Horizon 2020 research and innovation programme under Grant Agreements No. 
 * 691567 (GN4-1) and No. 731122 (GN4-2).
 * On behalf of the aforementioned projects, GEANT Association is the sole owner
 * of the copyright in all material which was developed by a member of the GÉANT
 * project. GÉANT Vereniging (Association) is registered with the Chamber of 
 * Commerce in Amsterdam with registration number 40535155 and operates in the 
 * UK as a branch of GÉANT Vereniging.
 * 
 * Registered office: Hoekenrode 3, 1102BR Amsterdam, The Netherlands. 
 * UK branch address: City House, 126-130 Hills Road, Cambridge CB2 1PQ, UK
 *
 * License: see the web/copyright.inc.php file in the file structure or
 *          <base_url>/copyright.php after deploying the software
 */

/* General function for doing HTTP XML GET requests. */

function getXML(attribute_class) {
    var client = new XMLHttpRequest();
    client.attribute_class = attribute_class;
    client.onreadystatechange = addOption;
    client.open("GET", "inc/option_xhr.inc.php?class=" + attribute_class + "&etype=XML");
    client.send();
}

function addOption() {
    if (this.readyState === 4 && this.status === 200) {
        var field = document.getElementById("expandable_" + this.attribute_class + "_options");
        var div = document.createElement('tbody');
        div.innerHTML = this.responseText;
        field.appendChild(div.firstChild);
    }
}

function processCredentials() {
    if (this.readyState === 4 && this.status === 200) {
        var field = document.getElementById("disposable_credential_container");
        field.innerHTML = this.responseText;
    }
}

function doCredentialCheck(form) {
    postXML(processCredentials, form);
}

function deleteOption(identifier) {
    var field = document.getElementById(identifier);
    field.parentNode.removeChild(field);
}

function MapGoogleDeleteCoord(e) {
    marks[e - 1].setOptions({visible: false});
}