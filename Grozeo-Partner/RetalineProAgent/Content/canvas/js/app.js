let canvas = new fabric.Canvas("canvas");

let baseWidth = 0;
let baseHeight = 0;
let x = 0;
let y = 0;
let zoomPercent = 100;
let currentFont = "Montserrat";
var fonts = ["Montserrat",
    "Poppins",
    "Raleway",
    "Roboto",
    "Open Sans", "Lato"];


/*let getImagesUrl = "https://localhost:44306/Content/canvas/customimages.json";*/

let getImagesUrl = "/api/home/GetGraphicsObjects"; //"/Content/canvas/customimages.json";

getTemplateImages();


//function getTemplateImages() {
//    fetch(getImagesUrl)
//        .then(r => r.json())
//        .then(function (res) {
//            let images = [];
//            let textElements = [];

//            serverData.Images.forEach(function (img) {
//                if (img.name == "Template") {
//                    loadBgImage(img.url);
//                } else if (img.name == "QRCode") {
//                    // Add QR code only once
//                    images.push('<a class="col-12 img-block text-center border-bottom py-3 mb-2"><img draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)" class="source-img" style="max-width:100px;" src="' + dynamicQrCodeUrl + '" width="100%"></a>');
//                }

//                else if (img.name == "UserLogo") {
//                    images.push('<a class="col-12 img-block text-center border-bottom py-3 mb-2"><img draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)" class="source-img" src="' + img.url + '" width="100%"></a>');
//                }
//            });

//            document.getElementById("template-images").innerHTML = images.join("");

//            textElements.push('<div class="col-12 text-block card border-bottom border-top py-3 mb-2 text-center" draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)">' + serverData.storename + '</div>');
//            textElements.push('<div class="col-12 text-block card border-bottom border-top py-3 mb-2 text-center" draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)">' + serverData.address + '</div>');
//            textElements.push('<div class="col-12 text-block card border-bottom border-top py-3 mb-2 text-center" draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)">' + serverData.email + '</div>');
//            textElements.push('<div class="col-12 text-block card border-bottom border-top py-3 mb-2 text-center" draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)">' + serverData.websiteurl + '</div>');
//            textElements.push('<div class="col-12 text-block card border-bottom border-top py-3 mb-2 text-center" draggable="true" ondragend="onImageDragEnd(event)" ondragover="onImageDragStart(event)">' + serverData.phone + '</div>');

//            document.getElementById("template-text").innerHTML = textElements.join("");
//        });
//}


function getTemplateImages() {
    fetch(getImagesUrl)
        .then(r => r.json())
        .then(function (res) {
            let images = [];
            let textElements = [];
            let selectOptions = ''; // String to store select options

            // Add "Insert Element" option
            selectOptions += '<option value="InsertElement">Insert Element</option>';

            serverData.Images.forEach(function (img) {
                if (img.name == "Template") {
                    loadBgImage(img.url);
                }
                else if (img.name == "QRCode") {
                    selectOptions += '<option value="' + dynamicQrCodeUrl + '">QR Code</option>';
                }
                if (img.name == "UserLogo") {
                    if (img.url != "") {
                        selectOptions += '<option value="' + img.url + '">Logo</option>';
                    } else {
                        img.url = "";
                        alert("No logo uploaded");
                    }
                }
            });

            // Populate the select element with options
            const selElement = document.getElementById("selElement");
            selElement.innerHTML = selectOptions;

            // Attach event listener to the select element for selection
            selElement.addEventListener("change", function () {
                const selectedValue = this.value;
                if (selectedValue) {
                    selectImage(selectedValue);
                }
            });
        })
        .catch(error => console.error("Error fetching or processing data:", error));
}

function selectImage(url) {
    let imgObj = new Image();
    imgObj.crossOrigin = "anonymous";
    imgObj.src = url;
    imgObj.onload = function () {
        let image = new fabric.Image(imgObj);
        image.set({
            left: 100,
            top: 100,
            borderColor: 'red',
            cornerColor: 'red',
            type: "image",
        });
        if (imgObj.width > 100) {
            image.scaleToWidth(150, false);
        }

        canvas.add(image);
        canvas.setActiveObject(image);
    };
}

function hideImagePicker() {
    setTimeout(function () {
        document.getElementById('image-picker').style.display = 'none';
    }, 100);
} 

//function loadBgImage(imgUrl) {
//    let img = new Image();
//    img.crossOrigin = "anonymous";
//    img.src = imgUrl;
//    let canvasBase = document.getElementById("canvas-base");

//    img.onload = function () {
//        baseHeight = img.height;
//        baseWidth = img.width;
//        canvas.setWidth(baseWidth);
//        canvas.setHeight(baseHeight);
//        canvas.setBackgroundImage(new fabric.Image(img, {
//            originX: 'left',
//            originY: 'top',
//            left: 0,
//            top: 0
//        }), canvas.renderAll.bind(canvas));

//        var hRatio = canvasBase.clientWidth / img.width;
//        var vRatio = canvasBase.clientHeight / img.height;
//        var ratio = Math.min(hRatio, vRatio);
//        changeZoom(ratio * 100);
//    };
//}

function loadBgImage(imgUrl) {
    let img = new Image();
    img.crossOrigin = "anonymous";
    img.src = imgUrl;
    let canvasBase = document.getElementById("canvas-base");

    img.onload = function () {
        baseHeight = img.height;
        baseWidth = img.width;
        canvas.setWidth(baseWidth);
        canvas.setHeight(baseHeight);
        canvas.setBackgroundImage(new fabric.Image(img, {
            originX: 'left',
            originY: 'top',
            left: 0,
            top: 0
        }), canvas.renderAll.bind(canvas));

        var hRatio = canvasBase.clientWidth / img.width;
        var vRatio = canvasBase.clientHeight / img.height;
        var ratio = Math.min(hRatio, vRatio);
        changeZoom(ratio * 100);
    };
}

var select = document.getElementById("font-family");
fonts.forEach(function (font) {
    var option = document.createElement('option');
    option.innerHTML = font;
    option.value = font;
    select.appendChild(option);
});

document.getElementById('font-family').onchange = function () {
    if (this.value !== 'Times New Roman') {
        loadAndUse(this.value);
    } else {
        canvas.getActiveObject().set("fontFamily", this.value);
        canvas.requestRenderAll();
    }
};

function loadAndUse(font) {
    currentFont = font;
    var myfont = new FontFaceObserver(font)
    myfont.load()
        .then(function () {
            canvas.getActiveObject().set("fontFamily", font);
            canvas.requestRenderAll();
        }).catch(function (e) {
            console.log(e)
        });
}


function onImageDragStart(e) {
    canvas.discardActiveObject().renderAll();
}

//function onImageDragEnd(o) {
//    if (o.target) {
//        if (o.target.className === 'source-img') {
//            // Handle image drag
//            let imgObj = new Image();
//            imgObj.crossOrigin = "anonymous";
//            imgObj.src = o.target.currentSrc;
//            imgObj.onload = function () {
//                let image = new fabric.Image(imgObj);
//                image.set({
//                    left: 100,
//                    top: 100,
//                    borderColor: 'red',
//                    cornerColor: 'red',
//                    type: "image",
//                });
//                //if (o.target.width > 100) {
//                    image.scaleToWidth(100, false);
//                //}

//                canvas.add(image);
//                canvas.setActiveObject(image);
//            };
//        } else if (o.target.className.includes("text-block")) {
//            // Handle text block drag
//            let textEditable = new fabric.Textbox(
//                o.target.innerHTML, {
//                width: 300,
//                editable: true,
//                left: 100,
//                top: 100,
//                borderColor: 'red',
//                cornerColor: 'red',
//                type: 'text',
//                fontFamily: currentFont,
//                fontSize:16,
//                lineHeight: 1
//            });
//            canvas.add(textEditable);
//            canvas.setActiveObject(textEditable);
//        }
//    }
//}
//function onImageDragEnd(o) {
//    if (o.srcElement && o.srcElement.className === 'source-img') {
//        let imgObj = new Image();
//        imgObj.crossOrigin = "anonymous";
//        imgObj.src = o.srcElement.currentSrc;
//        imgObj.onload = function () {
//            let image = new fabric.Image(imgObj);
//            image.set({
//                left: o.clientX,
//                top: o.clientY,
//                borderColor: 'red',
//                cornerColor: 'red',
//                type: "image",
//            });
//            if (o.srcElement.width > 100) {
//                image.scaleToWidth(150, false);
//            }

//            canvas.add(image);
//            canvas.setActiveObject(image);
//        }
//    }
//    else if (o.originalTarget && o.originalTarget.className.includes("text-block")) {
//        let textEditable = new fabric.Textbox(
//            o.originalTarget.innerHTML, {
//            width: 300,
//            editable: true,
//            left: o.clientX,
//            top: o.clientY,
//            borderColor: 'red',
//            cornerColor: 'red',
//            type: 'text',
//            fontFamily: currentFont,
//            lineHeight: 1
//        });
//        canvas.add(textEditable);
//        canvas.setActiveObject(textEditable);
//    }
//}


let colorPicker = document.getElementById('text-color');
colorPicker.addEventListener('input', function () {
    let activeObject = canvas.getActiveObject();
    if (activeObject != null) {
        if (activeObject.type == "circle" || activeObject.type == "rectangle") {
            activeObject.set("stroke", colorPicker.value);
        }
        else {
            activeObject.set("fill", colorPicker.value);
        }
        canvas.renderAll();
    }
});

function addtextBox() {
    let textEditable = new fabric.Textbox(
        'Enter text here', {
        width: 300,
        editable: true,
        top: 50,
        left: 20,
        borderColor: 'red',
        cornerColor: 'red',
        type: 'text',
        fontFamily: currentFont,
        fontSize: 16,
        lineHeight: 1
    });
    canvas.add(textEditable);
    canvas.setActiveObject(textEditable);
}

function deleteObject() {
    canvas.remove(canvas.getActiveObject());
}

function drawCircle() {
    let circle = new fabric.Circle({
        left: 100,
        top: 100,
        radius: 50,
        borderColor: 'red',
        cornerColor: 'red',
        fill: 'transparent',
        stroke: 'red',
        type: 'circle'
    });
    canvas.add(circle);
    canvas.setActiveObject(circle);
}


function drawRectangle() {
    let rectangle = new fabric.Rect({
        left: 100,
        top: 100,
        width: 100,
        height: 100,
        borderColor: 'red',
        cornerColor: 'red',
        fill: 'transparent',
        stroke: 'red',
        type: 'rectangle'
    });
    canvas.add(rectangle);
    canvas.setActiveObject(rectangle);
}

function changeZoom(val) {
    zoomPercent = val / 100;
    document.getElementById("zoom-val").innerHTML = Math.ceil(val) + " %";
    document.getElementById("zoom").value = val;
    canvas.setZoom(zoomPercent);
    canvas.setWidth(baseWidth * canvas.getZoom());
    canvas.setHeight(baseHeight * canvas.getZoom());
}

function downloadImage() {
    canvas.discardActiveObject().renderAll();
    let dt = canvas.toDataURL({
        format: 'png',
        quality: 1,
    })
    let a = document.createElement('a')
    dt = dt.replace(/^data:image\/[^;]*/, 'data:application/octet-stream')
    dt = dt.replace(
        /^data:application\/octet-stream/,
        'data:application/octet-stream;headers=Content-Disposition%3A%20attachment%3B%20filename=Canvas.png',
    )
    a.href = dt
    a.download = 'download.png'
    a.click()
}