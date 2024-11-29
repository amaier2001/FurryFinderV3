#include "nmea.h"

void PARSER(char *src, GPS_DATA *GPRMC) //works when called from main but not from within SIM7070G_GPS_REQUEST
{
    int cont = 0;
    const char s[3] = ", ";
    char *token;

    token = strtok_new(src, s);

    while(token != NULL)
    {
        switch(cont)
        {
            case 0: //AT+CGNSINF\r\n+CGNSINF: [DISCARD]
                break;

            case 1: //run
                sscanf(token, "%d", &GPRMC->run);
                break;

            case 2: //fix_status
                sscanf(token, "%d", &GPRMC->fix_stat);
                break;

            case 3: //UTC datetime
                sscanf(token, "%4d%2d%2d%2d%2d%s", &GPRMC->year, &GPRMC->month, &GPRMC->day, &GPRMC->hour, &GPRMC->minute, GPRMC->seconds);
                break;

            case 4: //Latitude
                sscanf(token, "%s", GPRMC->latitude);
                break;

            case 5: //Longitude
                sscanf(token, "%s", GPRMC->longitude);
                break;
/*
            case 6: //MSL_A
                //sscanf(token, "%s", &GPRMC->MSL_A);
                break;

            case 7: //SOG
                //sscanf(token, "%s", &GPRMC->SOG);
                break;

            case 8: //COG
                //sscanf(token, "%s", &GPRMC->COG);
                break;

            case 9: //fix_mode
                //sscanf(token, "%d", &GPRMC->fix_mode);
                break;

            case 10: //RES1 [DISCARD]
                break;

            case 11: //HDOP
                //sscanf(token, "%s", &GPRMC->HDOP);
                break;

            case 12: //PDOP
                //sscanf(token, "%s", &GPRMC->PDOP);
                break;

            case 13: //VDOP
                //sscanf(token, "%s", &GPRMC->VDOP);
                break;

            case 14: //RES2 [DISCARD]
                break;

            case 15: //SIV
                //sscanf(token, "%d", &GPRMC->SIV);
                break;

            case 16: //RES3 [DISCARD]
                break;

            case 17: //HPA
                //sscanf(token, "%s", &GPRMC->HPA);
                break;

            case 18: //VPA+OK
                //sscanf(token, "%s", &GPRMC->VPA);
                break;
*/
            default:
                break;
        }

        //printf("String: \"%s\" -- Cont: %d\n\r", token, cont);
        cont++;
        token = strtok_new(NULL, s);
    }

/*
    printf("Year: %d, Month: %d, Day: %d, Hour: %d, Minute: %d, Second: %s\n", GPRMC->year, GPRMC->month, GPRMC->day, GPRMC->hour, GPRMC->minute, GPRMC->seconds);
    printf("Latitude: %s\n", GPRMC->latitude);
    printf("Longitude: %s\n", GPRMC->longitude);
*/
}

//taken from https://stackoverflow.com/questions/26522583/c-strtok-skips-second-token-or-consecutive-delimiter
char *strtok_new(char *string, char const *delimiter)
{
    static char *source = NULL;
    char *p, *riturn = 0;
    if (string != NULL)
        source = string;
    if (source == NULL)
        return NULL;

    if ((p = strpbrk(source, delimiter)) != NULL)
    {
        *p = 0;
        riturn = source;
        source = ++p;
    }
    else if (*source)
    {
        riturn = source;
        source = NULL;
    }
    return riturn;
}
