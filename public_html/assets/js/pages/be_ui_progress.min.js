/*!
 * dashmix - v5.4.0
 * @author pixelcave - https://pixelcave.com
 * Copyright (c) 2022
 */
Dashmix.onLoad((()=>class{static barsRandomize(){document.querySelectorAll(".js-bar-randomize").forEach((t=>{t.addEventListener("click",(e=>{t.closest(".block").querySelectorAll(".progress-bar").forEach((t=>{let e=Math.floor(91*Math.random()+10),a=t.querySelector("span");t.style.width=e+"%",a&&(a.textContent=e+"%")}))}))}))}static init(){this.barsRandomize()}}.init()));