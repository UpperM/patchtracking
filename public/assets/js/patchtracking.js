function callApi(endpoint,method)
{
    var data = "";
    $.ajax({
        url: "/api/" + endpoint, // URL de la page
        type: method, // GET ou POST
        data: {}, // ParamÃ¨tres envoyÃ©s Ã  php
        dataType: "json", // DonnÃ©es en retour
        contentType: "application/json",
        async: false,
        success: function (response) {
            data = response;
        }
    });
    return data;
}

/* -------------------------------------------------------- */
/* Fonctions de gestion de l'activiation ou pas des inputs */
/* -------------------------------------------------------- */
function disableElement(element)
{
    document.getElementById(element).disabled = true;
}

function enableElement(element)
{
    document.getElementById(element).disabled = false;
}

function showElement(element)
{
    //document.getElementById(element).style.visibility = 'visible';
    document.getElementById(element).classList.remove('invisible');
}

function hideElement(element)
{
  document.getElementById(element).classList.add('invisible');
}

function displayGithubUrl(element)
{

  if (element.checked == true)
  {
    enableElement("btnGithubVersion");
    showElement("githubForm");
  } else
  {
    disableElement("btnGithubVersion");
    hideElement("githubForm");
  }
}

function getApplicationCurrentVersion(application)
{
  var url = "/application/api/" + application + "/currentversion";
  var data = "";
  $.ajax({
      url: url, // URL de la page
      type: 'GET', // GET ou POST
      data: {}, // ParamÃ¨tres envoyÃ©s Ã  php
      dataType: "json", // DonnÃ©es en retour
      contentType: "application/json",
      async: false,
      success: function (response) {
          data = response;
      }
  });


  return data.current_version;
}


function getGithubVersion()
{

  var url = document.getElementById("settings_githubRepository").value;
  var url = url.replace(/\/$/, "");
  var url = url.replace("https://github.com/","");

  var data = "";
  $.ajax({
      url: "https://api.github.com/repos/" + url + "/releases/latest", // URL de la page
      type: 'GET', // GET ou POST
      data: {}, // ParamÃ¨tres envoyÃ©s Ã  php
      dataType: "json", // DonnÃ©es en retour
      contentType: "application/json",
      async: false,
      success: function (response) {
          data = response;
      }
  });

  var version = data.tag_name.replace("v","");
  //document.getElementById("form_latestVersion").value = version;
  $('#settings_latestVersion').val(version)
  return version;
}

function onSelectApi(selectedIndex) {
  var app = selectedIndex.options[selectedIndex.selectedIndex].value;
  var version = "";
  if (app) {
    var getVersion = getApplicationCurrentVersion(app);
    if (getVersion) {
      var version = getVersion;
    }
  }

  document.getElementById("settings_currentVersion").value = version;

}
/* -------------------------------------------------------- */
/* Fonctions de gestion de l'affichage des erreurs / succÃ¨s */
/* -------------------------------------------------------- */

function loadingBtnGithubVersion() {
  showElement("loadingGithubSpinner");
  document.getElementById('btnCheckGithubVersion')[0].removeAttribute('href');
}

function loadingBtnApplicationVersion() {
  showElement("loadingApplicationSpinner");
  document.getElementById('btnCheckApplicationVersion')[0].removeAttribute('href');
}

