(function () {
  var modal = document.querySelector("[data-gz-contact-modal]");

  function openContact() {
    if (modal) modal.classList.add("is-open");
  }

  function closeContact() {
    if (modal) modal.classList.remove("is-open");
  }

  document.querySelectorAll("[data-gz-open-contact]").forEach(function (button) {
    button.addEventListener("click", openContact);
  });

  document.querySelectorAll("[data-gz-close-contact]").forEach(function (button) {
    button.addEventListener("click", closeContact);
  });

  if (modal) {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) closeContact();
    });
  }

  document.querySelectorAll("[data-gz-copy]").forEach(function (button) {
    button.addEventListener("click", function () {
      var value = button.getAttribute("data-gz-copy") || "aiguozhanhuihua";
      if (!navigator.clipboard) {
        alert("当前浏览器不支持自动复制，请手动复制：" + value);
        return;
      }
      navigator.clipboard.writeText(value).then(function () {
        alert("已复制微信号");
      });
    });
  });

  var mainImage = document.getElementById("theMainImage");
  if (mainImage && !document.querySelector(".gz-picture-contact")) {
    var titleNode = document.querySelector("#imageHeaderBar h2, #breadcrumb h2, .titrePage h2");
    var code = titleNode ? titleNode.textContent.replace(/\s+/g, " ").trim() : document.title;
    var panel = document.createElement("div");
    panel.className = "gz-picture-contact";
    panel.innerHTML = '<p>作品编码：<strong>' + code + '</strong></p><p>咨询高清图或同类作品时，请发送此编码。</p><button type="button" data-gz-open-contact>联系客服</button>';
    mainImage.insertAdjacentElement("afterend", panel);
    panel.querySelector("[data-gz-open-contact]").addEventListener("click", openContact);
  }
})();
