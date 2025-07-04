
const dataPage = document.getElementById("app").getAttribute("data-page");
const page = JSON.parse(dataPage);
const name = page.props?.name || "Khong Truong";
const h1 = document.createElement("h1");
h1.innerText = "Xin chào, tôi là " + name;
h1.style = "text-align:center;margin-top:40px;color:white;font-size:24px;";
document.body.insertBefore(h1, document.body.firstChild);
