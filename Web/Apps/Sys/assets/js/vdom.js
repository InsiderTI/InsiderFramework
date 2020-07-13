document.addEventListener("DOMContentLoaded", function (event) {
  var vdomelements = document.getElementsByTagName("vdomdata")
  for (i = 0; i < vdomelements.length; i++) {
    vdome = vdomelements[i]
    console.log(vdome)
  }
  for (i = 0; i < vdomelements.length; i++) {
    vdome = vdomelements[i]
    vdome.remove()
  }
})
