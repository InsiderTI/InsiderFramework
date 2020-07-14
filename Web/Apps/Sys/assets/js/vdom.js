document.addEventListener("DOMContentLoaded", function (event) {
  var vdomelements = document.getElementsByTagName("vdomdata")
  while (vdomelements.length != 0) {
    vdome = vdomelements[0]
    var VDomContent = JSON.parse(vdome.innerHTML)
    eval(VDomContent)
    vdome.remove()
    vdomelements = document.getElementsByTagName("vdomdata")
  }
})
