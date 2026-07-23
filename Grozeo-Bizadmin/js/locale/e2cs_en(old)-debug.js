// --------------------------------------------------
// E2CS  Extjs-Event-Calendar-Solution  alpha(0.0.1) 
// @Version E2CS-0.0.1  june 1 2008 
// English
// @Author: Carlos Mendez  
// @contact: cmendez@mm-mendez.com, for MSN chat:cmendez21@hotmail.com
// --------------------------------------------------
//
// @license E2CS is licensed under the terms of
// the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
// that the code/component(s) do NOT become part of another Open Source or Commercially
// licensed development library or toolkit without explicit permission.
//
// --------------------------------------------------
Ext.namespace('e2cs.cal_locale');
e2cs.cal_locale={
	// Calendar locale	
	dateSelectorText:    'Select..',
	dateSelectorTooltip: 'Select a Date..',
	// Why i have my own locale for days and months you will ask ? 
	// i didn't want to mess with ext locale and prefer the widget will have his own locale	
	monthtitles:["January",	 "February",	 "March",
				 "April",	 "May",			 "June",
				 "July",	 "August",		 "September",
				 "October",	 "November",	 "December"],
	daytitles:  ["Sunday",	 "Monday",		 
				 "Tuesday",	 "Wednesday","Thursday",	 
				 "Friday",	 "Saturday"],
	
	todayLabel: 'Today',
	todayToolTip: 'Set Today date...',
	
	// toolbar buttons tooltips 
	tooltipMonthView:	'See Month view...',
	tooltipWeekView:	'See Week View...',	
	tooltipDayView:		'See Day View...',	
	
	// tpl for zoom tasks general labels 
	win_tasks_format:  'm-d-Y',
	win_tasks_loading: 'Loading...',
	win_tasks_empty:   'No Events...', 	 				//0.0.7  tasks changed to events
	// Month view locale
	win_month_zoomlabel:'Events in day', 				//0.0.7  tasks changed to events
	headerTooltipsMonth: { 
		prev: 'Previous month.', 
		next: 'Next Month.' 
	}, 
	contextMenuLabelsMonth: { 
		task: "Add new task for", 
		chgwview: "Change to week view...", 
		chgdview: "Change to day view...",
		gonextmonth: "Go to next month",     			// 0.0.4
		goprevmonth: "Go previous month"     			// 0.0.4
	},
	labelforTasksinMonth: 'Events in day:',				//0.0.7  tasks changed to events
	// Dayview and daytask  locale 	
	task_MoreDaysFromTask: '<br>(+)',
	task_LessDaysFromTask: '(-)<br>',
	task_qtip_starts: 	'Starts: ', 
	task_qtip_ends: 	'Ends: ', 	
	headerTooltipsDay: { 
			prev: 'Previous day', 
			next: 'Next day' 
	}, 
	contextMenuLabelsDay: { 
		taskAdd: "New Event", 							//0.0.7  tasks changed to events
		taskDelete: "Delete Event", 					//0.0.7  tasks changed to events
		taskEdit: "Edit Event:",						//0.0.7  tasks changed to events
		NextDay: "Go to next day", 
		PreviousDay: "Go to previous day",
		chgwview: "Change to week view...", 
		chgmview: "Change to Month view..."
	},
	//Week view  and weektask locale  // 0.0.4
	contextMenuLabelsWeek: { 
		prev: "Go to Previous week.", 
		next: "Go Next week.", 
		chgdview: "Change to day view...", 
		chgmview: "Change to month view..."
	},	
	//0.0.4
	headerTooltipsWeek: { 
		prev: 'Previous week.', 
		next: 'Next week.' 
	},
	labelforTasksinWeek: 'More Events on Week:',   		//0.0.7  tasks changed to events
	win_week_zoomlabel:'More Events...',				//0.0.7  tasks changed to events
	weekheaderlabel:'Week #',
	weekheaderlabel_from:'From:',
	weekheaderlabel_to:' To:',	
	alldayTasksMaxLabel:'See More All(+)day(s) events..' //0.0.7  tasks changed to events
}