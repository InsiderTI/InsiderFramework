warningImg = document.getElementById("warningImg")
warningImg.onclick = function (e) {
  warningItemList = document.getElementById("warningItemList")
  if (warningItemList.style.display !== "block")
    warningItemList.style.display = "block"
  else warningItemList.style.display = "none"
}

function getWarningItemHtml(warningObj) {
  var messageOrText = warningObj.message ? warningObj.message : warningObj.text
  var relativePath = warningObj.file
  var line = warningObj.line

  warningTemplate =
    "<div class='warningItem'>" +
    "  <div class='warningMessage'>" +
    messageOrText +
    "  </div>" +
    "  <div class='warningFile'>" +
    "      File: " +
    relativePath +
    "  </div>" +
    "  <div class='warningLine'>" +
    "      Line: " +
    line +
    "  </div>" +
    "</div>"

  return warningTemplate
}

function registerWarning(base64Warning) {
  var warningImg = document.getElementById("warningImg")
  if (warningImg.className == "warningImgOff") {
    warningImg.className = "warningImgOn"
  }

  var warningCounter = parseInt(
    document.getElementById("warningCounter").innerHTML
  )
  warningCounter++
  document.getElementById("warningCounter").innerHTML = warningCounter

  var warningObj = JSON.parse(atob(base64Warning))
  var htmlItem = getWarningItemHtml(warningObj)
  var warningItemList = document.getElementById("warningItemList")

  warningItemList.innerHTML += htmlItem
}