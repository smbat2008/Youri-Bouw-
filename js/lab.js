const cards = Array.from(document.querySelectorAll(".galley__card"));
const slider = document.querySelector(".slider");
const sliderContainer = document.querySelector(".slider__container");
const pictures = Array.from(document.querySelectorAll(".galley__card_pic"));
const sliderBtnLeft = document.querySelector(".slider__btn_left");
const sliderBtnRight = document.querySelector(".slider__btn_right");
const sliderClose = document.querySelector(".slider__close");

let cardIndex = -1;
let pictureFall = null; // Глобальная переменная для текущего изображения

// Открытие слайдера
cards.forEach((card, index) => {
    card.addEventListener("click", () => {
        cardIndex = index;
        updateSliderImage();
        slider.classList.add("active");
    });
});

// Функция обновления изображения в слайдере
function updateSliderImage() {
    let newPictureFall = pictures[cardIndex].cloneNode();
    newPictureFall.style.objectFit = "contain";

    if (pictureFall) {
        pictureFall.replaceWith(newPictureFall);
    } else {
        // Удаляем только картинку, оставляя стрелки
        let oldImage = sliderContainer.querySelector("img:not(.slider__btn img):not(.slider__close img)");
        if (oldImage) oldImage.remove();

        sliderContainer.append(newPictureFall);
    }

    pictureFall = newPictureFall; // Обновляем ссылку на текущее изображение
}

// Переключение картинок
sliderBtnLeft.addEventListener("click", () => changePicture("left"));
sliderBtnRight.addEventListener("click", () => changePicture("right"));

function changePicture(dir) {
    if (dir === "left") {
        cardIndex = (cardIndex > 0) ? cardIndex - 1 : pictures.length - 1;
    } else {
        cardIndex = (cardIndex < pictures.length - 1) ? cardIndex + 1 : 0;
    }
    updateSliderImage();
}

// Закрытие слайдера
sliderClose.addEventListener("click", () => {
    slider.classList.remove("active");
    let oldImage = sliderContainer.querySelector("img:not(.slider__btn img):not(.slider__close img)");
    if (oldImage) oldImage.remove();
    pictureFall = null;
});
