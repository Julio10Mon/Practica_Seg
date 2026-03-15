document.addEventListener("DOMContentLoaded", function() {
    const titleText = "SkillTrack AI";
    const subText = "Potencia tu carrera";
    const typingElement = document.getElementById("typing");
    const subtextElement = document.getElementById("subtext");
    const squares = document.querySelectorAll(".square");

    let index = 0;
    let subIndex = 0;

    function typeTitle() {
        if (typingElement && index < titleText.length) {
            typingElement.textContent += titleText[index];
            index++;
            setTimeout(typeTitle, 60);
        } else {
            typeSubtext();
        }
    }

    function typeSubtext() {
        if (subtextElement && subIndex < subText.length) {
            subtextElement.textContent += subText[subIndex];
            subIndex++;
            setTimeout(typeSubtext, 40);
        } else {
            squares.forEach(square => {
                square.style.animation = "none";
                square.style.opacity = "1";
            });
        }
    }

    typeTitle();
});