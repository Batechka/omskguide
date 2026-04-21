<?php
// Компонент FAQ для главной страницы
// Использует текущий язык из глобальной переменной $lang
?>
<section class="faq-section">
    <div class="container">
        <h2 class="faq-title"><?= $lang == 'ru' ? 'Часто задаваемые вопросы' : 'Frequently Asked Questions' ?></h2>
        <div class="faq-grid">
            <?php if ($lang == 'ru'): ?>
                <div class="faq-item">
                    <div class="faq-question">Что посмотреть в Омске за 1 день?</div>
                    <div class="faq-answer">За один день можно успеть посетить Омскую крепость, прогуляться по Любинскому проспекту, увидеть Успенский собор и сделать фото с памятником Степанычу. Рекомендуем начать с набережной Иртыша, а завершить день в Омском театре драмы.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Куда сходить в Омске с детьми?</div>
                    <div class="faq-answer">Для семейного отдыха отлично подойдут: Иртышская набережная с детскими площадками, парк «Зеленый остров», Омский цирк и музей «Экспериментарий». Зимой работают ледовые городки и катки.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Где в Омске сделать красивые фото?</div>
                    <div class="faq-answer">Лучшие фотолокации: Любинский проспект (особенно вечером с подсветкой), вид на Иртыш со смотровой площадки у Омской крепости, фасад Омского театра драмы и памятник Степанычу. Осенью живописны аллеи парка 30-летия ВЛКСМ.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Что посмотреть в Омске за 2 часа, если я в командировке?</div>
                    <div class="faq-answer">Если времени мало, сосредоточьтесь на историческом центре: Омская крепость, Любинский проспект и Успенский собор находятся в пешей доступности друг от друга. За 2 часа вы успеете охватить главные символы города.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Какие достопримечательности Омска бесплатные?</div>
                    <div class="faq-answer">Бесплатно можно посетить: Омскую крепость (территория открыта), Иртышскую набережную, Любинский проспект, памятник Степанычу, а также многие храмы (включая Успенский собор).</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Как добраться до центра Омска с ж/д вокзала?</div>
                    <div class="faq-answer">От железнодорожного вокзала до исторического центра можно доехать на автобусах № 24, 69, 109 или трамвае № 4. Время в пути около 15–20 минут. Такси обойдётся примерно в 150–200 рублей.</div>
                </div>
            <?php else: ?>
                <div class="faq-item">
                    <div class="faq-question">What to see in Omsk in 1 day?</div>
                    <div class="faq-answer">In one day you can visit Omsk Fortress, walk along Lyubinsky Prospect, see the Dormition Cathedral, and take a photo with the Styopanych monument. Start from the Irtysh Embankment and end at the Omsk Drama Theater.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Where to go in Omsk with children?</div>
                    <div class="faq-answer">Family-friendly places: Irtysh Embankment with playgrounds, "Green Island" park, Omsk Circus, and the "Experimentarium" science museum. Ice towns and skating rinks operate in winter.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Where to take beautiful photos in Omsk?</div>
                    <div class="faq-answer">Best photo spots: Lyubinsky Prospect (especially illuminated in the evening), the view of the Irtysh from the Omsk Fortress observation point, the facade of the Omsk Drama Theater, and the Styopanych monument.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What to see in Omsk in 2 hours on a business trip?</div>
                    <div class="faq-answer">Focus on the historic center: Omsk Fortress, Lyubinsky Prospect, and Dormition Cathedral are within walking distance. You can cover the main symbols of the city in 2 hours.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Which Omsk attractions are free?</div>
                    <div class="faq-answer">Free to visit: Omsk Fortress (territory), Irtysh Embankment, Lyubinsky Prospect, Styopanych monument, and many churches (including Dormition Cathedral).</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How to get to Omsk city center from the railway station?</div>
                    <div class="faq-answer">Buses No. 24, 69, 109 or tram No. 4 take you to the historic center in 15–20 minutes. Taxi costs about 150–200 RUB.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Микроразметка Schema.org для FAQ -->

<script>
    document.querySelectorAll('.faq-item').forEach(item=>{
    item.onclick=()=>item.classList.toggle('active');
});
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php if ($lang == 'ru'): ?>
      {
        "@type": "Question",
        "name": "Что посмотреть в Омске за 1 день?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "За один день можно успеть посетить Омскую крепость, прогуляться по Любинскому проспекту, увидеть Успенский собор и сделать фото с памятником Степанычу. Рекомендуем начать с набережной Иртыша, а завершить день в Омском театре драмы."
        }
      },
      {
        "@type": "Question",
        "name": "Куда сходить в Омске с детьми?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Для семейного отдыха отлично подойдут: Иртышская набережная с детскими площадками, парк «Зеленый остров», Омский цирк и музей «Экспериментарий». Зимой работают ледовые городки и катки."
        }
      },
      {
        "@type": "Question",
        "name": "Где в Омске сделать красивые фото?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Лучшие фотолокации: Любинский проспект (особенно вечером с подсветкой), вид на Иртыш со смотровой площадки у Омской крепости, фасад Омского театра драмы и памятник Степанычу. Осенью живописны аллеи парка 30-летия ВЛКСМ."
        }
      },
      {
        "@type": "Question",
        "name": "Что посмотреть в Омске за 2 часа, если я в командировке?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Если времени мало, сосредоточьтесь на историческом центре: Омская крепость, Любинский проспект и Успенский собор находятся в пешей доступности друг от друга. За 2 часа вы успеете охватить главные символы города."
        }
      },
      {
        "@type": "Question",
        "name": "Какие достопримечательности Омска бесплатные?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Бесплатно можно посетить: Омскую крепость (территория открыта), Иртышскую набережную, Любинский проспект, памятник Степанычу, а также многие храмы (включая Успенский собор)."
        }
      },
      {
        "@type": "Question",
        "name": "Как добраться до центра Омска с ж/д вокзала?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "От железнодорожного вокзала до исторического центра можно доехать на автобусах № 24, 69, 109 или трамвае № 4. Время в пути около 15–20 минут. Такси обойдётся примерно в 150–200 рублей."
        }
      }
    <?php else: ?>
      {
        "@type": "Question",
        "name": "What to see in Omsk in 1 day?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "In one day you can visit Omsk Fortress, walk along Lyubinsky Prospect, see the Dormition Cathedral, and take a photo with the Styopanych monument. Start from the Irtysh Embankment and end at the Omsk Drama Theater."
        }
      },
      {
        "@type": "Question",
        "name": "Where to go in Omsk with children?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Family-friendly places: Irtysh Embankment with playgrounds, Green Island park, Omsk Circus, and the Experimentarium science museum. Ice towns and skating rinks operate in winter."
        }
      },
      {
        "@type": "Question",
        "name": "Where to take beautiful photos in Omsk?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Best photo spots: Lyubinsky Prospect (especially illuminated in the evening), the view of the Irtysh from the Omsk Fortress observation point, the facade of the Omsk Drama Theater, and the Styopanych monument."
        }
      },
      {
        "@type": "Question",
        "name": "What to see in Omsk in 2 hours on a business trip?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Focus on the historic center: Omsk Fortress, Lyubinsky Prospect, and Dormition Cathedral are within walking distance. You can cover the main symbols of the city in 2 hours."
        }
      },
      {
        "@type": "Question",
        "name": "Which Omsk attractions are free?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Free to visit: Omsk Fortress (territory), Irtysh Embankment, Lyubinsky Prospect, Styopanych monument, and many churches (including Dormition Cathedral)."
        }
      },
      {
        "@type": "Question",
        "name": "How to get to Omsk city center from the railway station?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Buses No. 24, 69, 109 or tram No. 4 take you to the historic center in 15–20 minutes. Taxi costs about 150–200 RUB."
        }
      }
    <?php endif; ?>
  ]
}


</script>
