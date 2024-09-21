import { $t } from "@/plugins/i18n";
import { helpers, maxLength, minLength, required } from "@vuelidate/validators";
const alphaSpace = RegExp(/^[a-zA-Z\s]*$/);

const firstname = {
    required: helpers.withMessage($t("validation.required"), required),
    maxLength: 255,
    minLength: minLength(3),
    alphaSpace,
};
const lastname = {
    required: helpers.withMessage($t("validation.required"), required),
    maxLength: 255,
    minLength: minLength(3),
    alphaSpace,
};

export { alphaSpace, firstname, lastname };
