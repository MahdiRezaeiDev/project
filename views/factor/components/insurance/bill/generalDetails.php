<div class="flex gap-5 avoid_break" style="margin-top: 20px;">
    <div class=" description-box flex-grow">
        <div class="tahvilgirande-box-header">توضیحات فاکتور</div>
        <div style="min-height: 40px;" class="tahvilgirande-box-inner text-md text-center p-5 text-gray-600 font-semibold" id="description">
            خواهشمند است در زمان عودت کالای امانتی، این برگه را نیز به همراه داشته باشید تا فرآیند تحویل و تطبیق به‌درستی انجام شود.
        </div>
    </div>
</div>

<div class="footer-box">
    <p class="footer-box-adress">
        نظامیه ، بن بست ویژه ، پلاک ۴
    </p>
    <p style="direction: ltr !important;" class="footer-box-tell">
        <?php if ($factorType == 'korea'): ?>
            <span>
                ۰۲۱ - ۳۳ ۹۲ ۵۴ ۱۱
            </span>
            <span>
                ۰۹۳۰ - ۳۱۵ ۰۶ ۹۴
            </span>
        <?php elseif ($factorType == 'partner'): ?>
            <span style="direction: ltr !important;">
                ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۲
            </span>
            <span style="direction: ltr !important;">
                ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۳
            </span>
            <span style="direction: ltr !important;">
                ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۴
            </span>
        <?php elseif ($factorType == 'yadak'): ?>
            <span style="direction: ltr !important;">
                ۰۲۱ - ۳۳ ۹۷ ۹۳ ۷۰
            </span>
            <span style="direction: ltr !important;">
                ۰۲۱ - ۳۳ ۹۴ ۶۷ ۸۸
            </span>
            <span style="direction: ltr !important;">
                ۰۹۱۲ - ۰۸۱ ۸۳ ۵۵
            </span>
        <?php endif; ?>
    </p>
</div>