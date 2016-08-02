import priorityNav from 'priority-nav';
import footerMap from './modules/footer-map';
import projectTable from './modules/project-table';
const config = TerminallyPixelated;

priorityNav.init({
    mainNavWrapper: '.main-nav',
    navDropdownLabel: 'More&hellip;',
    navDropdownBreakpointLabel: 'Menu',
});

footerMap();

projectTable();
