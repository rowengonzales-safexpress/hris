/**
 * This file contains all the global methods,constants used throughout the application
 */


export const MonthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

export const ActiveInactiveText = {
  "I":'Inactive',
  "A":'Active',
}
let AppConfig = {};
import moment from "moment/moment";
export const core = {
  monthNames() {
    return MonthNames
  },

  setAppConfig(configs) {
    AppConfig = configs;
  },
  getAppConfig() {
    return AppConfig;
  },
  getDaysDifference(fromTimeStamp,toTimeStamp) {
    let diff = toTimeStamp - fromTimeStamp;
    return Math.ceil(diff / (1000 * 3600 * 24));
  },

  formatDate(date,format) {
      return moment(date).format(format);
  },

  //Format to Name of Month day, year (e.g Jan 01, 2000)
  formatDateToMonthDayYear(date) {
      return moment(date).format('MMMM Do YYYY, h:mm:ss a');
  },

  //format mm/dd/yyyy
  formatDateForDisplay(dateToFormat,formatAsText = false){
      return formatAsText ? moment(dateToFormat).format('MMM DD, YYYY') : moment(dateToFormat).format('MM/DD/YYYY');
  },
  //format yyyy-mm-dd
  formatDateToYMD(dateToFormat){
      return moment(dateToFormat).format('YYYY-MM-DD');
  },

  //get the current year
  getYearFromDate(date) {
      let dt = new Date(date);
      return  dt.getFullYear();
  },

  //get current date in YMD format
  getCurrentDate(format = 'yyyy-mm-dd'){
      if (format === 'yyyy-mm-dd') return this.formatDateToYMD(new Date())
      if (format === 'mm/dd/yyyy') return this.formatDateForDisplay(new Date())

      return new Date()
  },

  gotoElement(elementID,scrollBehavior='smooth') {
      if (!document.getElementById(elementID)) return;
      document.getElementById(elementID).scrollIntoView({behavior:scrollBehavior});
      document.getElementById(elementID).focus();
  },

  // get rounding number
  getRoundingNumber(decimalPlaces) {
    let roundingNumber = '1';
    for (let i = 1; i <= decimalPlaces; i++) {
      roundingNumber += "0";
    }
    return parseInt(roundingNumber)
  },

  mathRound(value,decimalPlaces = 1) {
    if (isNaN(parseFloat(value)) || isNaN(parseInt(decimalPlaces))) return NaN

    let rounded = Math.round( parseFloat(value) * this.getRoundingNumber(decimalPlaces)) / this.getRoundingNumber(decimalPlaces)
    let retVal = rounded

    // when decimal places is 2, ignore rounded value is greater than 0 and decimal value if lesss than .01
    let floor = Math.floor(rounded)
    if (floor > 0 && (rounded - floor) < 0.02 && decimalPlaces == 2) {
        retVal = floor
    } 
    // Because of the variance in floating decimal point we need to round the number to proximity of 0.99
    if ((rounded - floor) > 0.98) {
        retVal = Math.ceil(rounded)
    }

    return retVal
  },

  isValidPositiveNumber(value) {
    return value && parseFloat(value) >= 0 && isNaN(value) === false
  },

  isValueWithinMinMax(value,min,max,operator='') {
    if (!value) return false
    let result = true
    if (min && max) {
        if (min === max) {
            if (this.mathRound(value, 4) !== this.mathRound(min, 4)) {
                result = false;
            }
        } else {
            if (value < min) {
                result = false;
            } else if (value > max) {
                result = false;
            }
        }
    } else if (min) {
        if (operator && operator === '>') {
            result = !(value <= min)
        } else if (operator && operator === '≥') {
            result = !(value < min)
        } else if (value < min) {
            result = false;
        }
    } else if (max) {
        if (operator && operator === '<') {
            result = !(value >= max)
        } else if (operator && operator === '≤') {
            result = !(value > max)
        } else if (value > max) {
            result = false;
        }
    }
    return result
  },

  cleanString(string) {
    return string.replace(/[\s\/]/g, '')
  },

  //Format to Name of Month day, year (e.g Jan 01, 2000)
  formatDateTimeToPhraseOldSchool(date, hideTime = false) {
    let d = new Date(date);

    let ye = new Intl.DateTimeFormat('en', {year: 'numeric'}).format(d);
    let mo = new Intl.DateTimeFormat('en', {month: 'short'}).format(d);
    let da = new Intl.DateTimeFormat('en', {day: '2-digit'}).format(d);
    let time = new Intl.DateTimeFormat('en', {hour: '2-digit', minute: '2-digit'}).format(d);
    return hideTime ? `${mo} ${da}, ${ye}` : `${mo} ${da}, ${ye} ${time}`;
  },

  //format mm/dd/yyyy
  formatDateForDisplayOldSchool(dateToFormat, formatAsText = false) {
    //will add Time T00:00:00 to ensure the date specific value is not affected from timezone
    let date = dateToFormat.toString().length <= 10 ? new Date(dateToFormat + 'T00:00:00') : new Date(dateToFormat);
    return formatAsText ?
      // January 31, 2022
      (MonthNames[date.getMonth()] + ' ' + ("0" + date.getDate()).slice(-2) + ', ' + date.getFullYear()) :
      // 01/31/2022
      (date.getFullYear() + '/' + ("0" + (date.getMonth() + 1)).slice(-2) + '/' + ("0" + date.getDate()).slice(-2) );
  },

  //format yyyy-mm-dd
  formatDateToYMDOldSchool(dateToFormat) {
    //will add Time T00:00:00 to ensure the date specific value is not affected from timezone
    let date = dateToFormat.toString().length <= 10 ? new Date(dateToFormat + 'T00:00:00') : new Date(dateToFormat);
    return date.getFullYear() + '-' + ("0" + (date.getMonth() + 1)).slice(-2) + '-' + ("0" + date.getDate()).slice(-2)
  },

 
  getStatusText(value,valueObjectEnum) {
    if (valueObjectEnum.hasOwnProperty(value)) return valueObjectEnum[value]
    return value
  },
  getActiveInactiveText(status) {
    return ActiveInactiveText[status];
  },

  //converting newline to <br>
  n2lbr(text) {
      return text.split('\n')
      .reduce((accumulator, string) => {
      if (accumulator.length === 0) {
          return string
      }
      return accumulator.concat('<br>', string)
      }, [])
  },

};