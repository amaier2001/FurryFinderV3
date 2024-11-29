#ifndef NMEA_H_
#define NMEA_H_

#include <stdint.h>
#include <stdio.h>
#include <string.h>
#include "stm32f1xx_hal.h"

#define GPS_MAX 122 //94 (MAX GPS) + 28 (PRE-RESPONSE)

typedef struct {
	int run, fix_stat, year, month, day, hour, minute, batt/*, fix_mode, SIV*/;
	char seconds[7];
	char latitude[11];
	char longitude[12];
	//char MSL_A[9];
	//char SOG[7];
	//char COG[7];
	//char HDOP[5];
	//char PDOP[5];
	//char VDOP[5];
	//char HPA[7];
	//char VPA[7];
} GPS_DATA;

void PARSER(char *, GPS_DATA *);
char *strtok_new(char*, const char*);

#endif /* NMEA_H_ */
