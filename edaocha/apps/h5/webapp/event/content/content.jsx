import React, {Component} from 'react';

class EventContent extends Component
{
  render() {
    return(
      <div style={styles.contentBox}>
          <div style={styles.title}>活动详情</div>
          <div id="htmlMain" dangerouslySetInnerHTML={{__html: this.props.content}} />
      </div>
    );
  }
}

const styles = {
  contentBox: {
    backgroundColor: '#fff',
    borderTop: '1px solid #e5e5e5',
    marginTop: 10,
  },
  title: {
    borderBottom: '1px solid  #e5e5e5',
    width: '100%',
    lineHeight: '50px',
    height: 50,
    textIndent: '1em',
  },
}
export default EventContent;
