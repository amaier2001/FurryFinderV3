#ifndef SIM7070G_H_
#define SIM7070G_H_
//Includes
#include "stm32f1xx_hal.h"

//Defines


//Functions
void SIM7070G_COMMAND(char **, uint8_t);
void SIM7070G_COMMAND_SINGLE(char *);
void SIM7070G_INIT();
void SIM7070G_GPS_REQUEST();
void SIM7070G_BATT_REQUEST();
void SIM7070G_UPDATE(char*);

#endif /* INC_SIM7070G_H_ */
